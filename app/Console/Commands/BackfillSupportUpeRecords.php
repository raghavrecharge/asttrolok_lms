<?php

namespace App\Console\Commands;

use App\Models\NewSupportForAsttrolok;
use App\Services\SupportUpeBridge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Backfill UPE records for historically completed support tickets.
 *
 * Before the SupportUpeBridge fix, AdminSupportController created legacy Sale
 * records but no UPE records. This command retroactively creates the missing
 * UPE records so AccessEngine recognises the granted access.
 *
 * Safe to run multiple times — every SupportUpeBridge method is idempotent.
 */
class BackfillSupportUpeRecords extends Command
{
    protected $signature = 'support:backfill-upe
                            {--dry-run : Show what would be done without making changes}
                            {--scenario= : Only backfill a specific scenario}
                            {--id= : Backfill a single ticket by ID}';

    protected $description = 'Create missing UPE records for historically completed support tickets';

    private const BACKFILLABLE_SCENARIOS = [
        'relatives_friends_access',
        'mentor_access',
        'temporary_access',
        'course_extension',
        'offline_cash_payment',
        'free_course_grant',
        'refund_payment',
    ];

    public function handle(SupportUpeBridge $bridge): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('=== DRY RUN — no changes will be made ===');
        }

        $query = NewSupportForAsttrolok::query()
            ->whereIn('status', ['completed', 'executed', 'closed'])
            ->whereIn('support_scenario', self::BACKFILLABLE_SCENARIOS)
            ->whereNotNull('user_id')
            ->whereNotNull('webinar_id');

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }

        if ($this->option('scenario')) {
            $scenario = $this->option('scenario');
            if (!in_array($scenario, self::BACKFILLABLE_SCENARIOS)) {
                $this->error("Invalid scenario: {$scenario}");
                $this->line('Valid: ' . implode(', ', self::BACKFILLABLE_SCENARIOS));
                return 1;
            }
            $query->where('support_scenario', $scenario);
        }

        $tickets = $query->orderBy('id')->get();

        $this->info("Found {$tickets->count()} completed tickets to backfill.");

        if ($tickets->isEmpty()) {
            $this->info('Nothing to do.');
            return 0;
        }

        $stats = ['success' => 0, 'skipped' => 0, 'failed' => 0];

        $this->withProgressBar($tickets, function ($ticket) use ($bridge, $dryRun, &$stats) {
            try {
                if ($dryRun) {
                    $stats['success']++;
                    return;
                }

                $adminId = $ticket->executed_by ?? $ticket->verified_by ?? $ticket->support_handler_id ?? 1;

                $this->backfillTicket($bridge, $ticket, $adminId);
                $stats['success']++;

            } catch (\Throwable $e) {
                $stats['failed']++;
                Log::error('Backfill failed for ticket #' . $ticket->id, [
                    'scenario' => $ticket->support_scenario,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->newLine(2);
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total processed', $tickets->count()],
                ['Success', $stats['success']],
                ['Skipped', $stats['skipped']],
                ['Failed', $stats['failed']],
            ]
        );

        if ($stats['failed'] > 0) {
            $this->warn("Check laravel.log for details on {$stats['failed']} failures.");
        }

        return $stats['failed'] > 0 ? 1 : 0;
    }

    private function backfillTicket(SupportUpeBridge $bridge, NewSupportForAsttrolok $ticket, int $adminId): void
    {
        $userId = $ticket->user_id;
        $webinarId = $ticket->webinar_id;
        $supportId = $ticket->id;

        switch ($ticket->support_scenario) {
            case 'relatives_friends_access':
                $bridge->grantRelativeAccess($userId, $webinarId, $supportId, $adminId);
                break;

            case 'mentor_access':
                $bridge->grantMentorAccess($userId, $webinarId, $supportId, $adminId);
                break;

            case 'temporary_access':
                $days = $ticket->temporary_access_days ?? 7;
                $percentage = $ticket->temporary_access_percentage ?? 100;
                $bridge->grantTemporaryAccess($userId, $webinarId, $supportId, $adminId, $days, $percentage);
                break;

            case 'course_extension':
                $days = $ticket->extension_days ?? 7;
                $bridge->grantCourseExtension($userId, $webinarId, $supportId, $adminId, $days);
                break;

            case 'offline_cash_payment':
                $cashAmount = (float) ($ticket->cash_amount ?? 0);
                if ($cashAmount > 0) {
                    $bridge->creditCashToWallet($userId, $supportId, $adminId, $cashAmount);
                }
                break;

            case 'free_course_grant':
                $targetCourseId = $ticket->target_course_id ?? $webinarId;
                // For free course grant, source_course_id users → target course
                // But for backfill, the legacy Sale records already exist per user.
                // We just ensure the ticket requester has UPE access to target.
                $bridge->grantFreeCourseAccess($userId, $targetCourseId, $supportId, $adminId);
                break;

            case 'refund_payment':
                $bridge->recordRefund($userId, $webinarId, $supportId, $adminId);
                break;

        }
    }
}
