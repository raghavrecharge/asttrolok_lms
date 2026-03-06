<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewSupportForAsttrolok;
use App\Models\SupportAuditLog;
use App\Models\NewSupportForAsttrolokLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportLogController extends Controller
{
    /**
     * Display the support log viewer with filters.
     */
    public function index(Request $request)
    {
        $query = NewSupportForAsttrolok::with(['user', 'webinar', 'supportHandler', 'subAdmin'])
            ->orderBy('created_at', 'desc');

        // Search by ticket number
        if ($request->filled('ticket')) {
            $query->where('ticket_number', 'like', '%' . $request->ticket . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by scenario
        if ($request->filled('scenario')) {
            $query->where('support_scenario', $request->scenario);
        }

        // Filter by user (name or email)
        if ($request->filled('user_search')) {
            $search = $request->user_search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                })
                ->orWhere('guest_name', 'like', "%{$search}%")
                ->orWhere('guest_email', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by flow type
        if ($request->filled('flow_type')) {
            $query->where('flow_type', $request->flow_type);
        }

        $tickets = $query->paginate(25)->appends($request->all());

        // Status counts for summary
        $statusCounts = NewSupportForAsttrolok::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $scenarios = NewSupportForAsttrolok::select('support_scenario')
            ->whereNotNull('support_scenario')
            ->distinct()
            ->pluck('support_scenario')
            ->toArray();

        // If AJAX request, return only the table partial
        if ($request->ajax()) {
            return view('admin.support-logs.partials.tickets-table', compact('tickets'));
        }

        return view('admin.support-logs.index', compact('tickets', 'statusCounts', 'scenarios'));
    }

    /**
     * Display timeline for a specific ticket.
     */
    public function timeline($ticketId)
    {
        $ticket = NewSupportForAsttrolok::where('ticket_number', $ticketId)
            ->orWhere('id', $ticketId)
            ->firstOrFail();

        $ticket->load(['user', 'webinar', 'supportHandler', 'subAdmin']);

        // Get audit logs
        $auditLogs = SupportAuditLog::where('support_request_id', $ticket->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get activity logs
        $activityLogs = NewSupportForAsttrolokLog::where('support_request_id', $ticket->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Merge and sort all events chronologically
        $timeline = collect();

        foreach ($auditLogs as $log) {
            $timeline->push([
                'type' => 'audit',
                'timestamp' => $log->created_at,
                'action' => $log->action,
                'role' => $log->role,
                'old_status' => $log->old_status,
                'new_status' => $log->new_status,
                'user_name' => $log->user ? $log->user->full_name : 'System',
                'ip_address' => $log->ip_address,
                'metadata' => $log->metadata,
            ]);
        }

        foreach ($activityLogs as $log) {
            $timeline->push([
                'type' => 'activity',
                'timestamp' => $log->created_at,
                'action' => $log->action,
                'remarks' => $log->remarks,
                'old_data' => $log->old_data,
                'new_data' => $log->new_data,
                'ip_address' => $log->ip_address,
            ]);
        }

        $timeline = $timeline->sortBy('timestamp')->values();

        return view('admin.support-logs.timeline', compact('ticket', 'timeline'));
    }

    /**
     * Display dashboard with charts and stats.
     */
    public function dashboard()
    {
        // Status distribution
        $statusDistribution = NewSupportForAsttrolok::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Scenario distribution
        $scenarioDistribution = NewSupportForAsttrolok::select('support_scenario', DB::raw('count(*) as count'))
            ->whereNotNull('support_scenario')
            ->groupBy('support_scenario')
            ->pluck('count', 'support_scenario')
            ->toArray();

        // Daily ticket creation (last 30 days)
        $dailyTickets = NewSupportForAsttrolok::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Average resolution time (pending to executed)
        $avgResolutionHours = SupportAuditLog::where('new_status', 'executed')
            ->whereNotNull('created_at')
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, (SELECT created_at FROM new_support_for_asttrolok WHERE id = support_audit_logs.support_request_id), support_audit_logs.created_at)'));

        // Top handlers
        $topHandlers = NewSupportForAsttrolok::select('support_handler_id', DB::raw('count(*) as count'))
            ->whereNotNull('support_handler_id')
            ->groupBy('support_handler_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('supportHandler')
            ->get();

        // Flow type distribution
        $flowDistribution = NewSupportForAsttrolok::select('flow_type', DB::raw('count(*) as count'))
            ->whereNotNull('flow_type')
            ->groupBy('flow_type')
            ->pluck('count', 'flow_type')
            ->toArray();

        // Total counts
        $totalTickets = NewSupportForAsttrolok::count();
        $pendingTickets = NewSupportForAsttrolok::where('status', 'pending')->count();
        $executedTickets = NewSupportForAsttrolok::where('status', 'executed')->count();
        $rejectedTickets = NewSupportForAsttrolok::where('status', 'rejected')->count();

        return view('admin.support-logs.dashboard', compact(
            'statusDistribution',
            'scenarioDistribution',
            'dailyTickets',
            'avgResolutionHours',
            'topHandlers',
            'flowDistribution',
            'totalTickets',
            'pendingTickets',
            'executedTickets',
            'rejectedTickets'
        ));
    }

    /**
     * Export filtered tickets as CSV.
     */
    public function export(Request $request)
    {
        $query = NewSupportForAsttrolok::with(['user', 'webinar'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->get();

        $filename = 'support_tickets_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Ticket #', 'Status', 'Scenario', 'User', 'Email',
                'Course', 'Flow Type', 'Created At', 'Executed At',
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->ticket_number,
                    $ticket->status,
                    $ticket->support_scenario,
                    $ticket->getRequesterName(),
                    $ticket->getRequesterEmail(),
                    $ticket->webinar ? $ticket->webinar->title : 'N/A',
                    $ticket->flow_type,
                    $ticket->created_at->format('Y-m-d H:i:s'),
                    $ticket->executed_at ? $ticket->executed_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
