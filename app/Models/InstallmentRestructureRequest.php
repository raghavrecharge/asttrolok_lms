<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\InstallmentOrder;
use App\Models\InstallmentStep;
use App\Models\Webinar;

class InstallmentRestructureRequest extends Model
{
    use SoftDeletes;

    protected $table = 'installment_restructure_requests';

    protected $fillable = [
        'installment_order_id',
        'installment_step_id',
        'user_id',
        'webinar_id',
        'product_id',
        'bundle_id',
        'reason',
        'original_amount',
        'original_deadline',
        'number_of_sub_steps',
        'sub_steps_data',
        'status',
        'reviewed_by',
        'admin_notes',
        'reviewed_at',
        'support_ticket_id',
        'attachments',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'sub_steps_data' => 'array',
        'attachments' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function installmentOrder()
    {
        return $this->belongsTo(InstallmentOrder::class, 'installment_order_id');
    }

    public function installmentStep()
    {
        return $this->belongsTo(InstallmentStep::class, 'installment_step_id');
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class, 'webinar_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function subSteps()
    {
        return $this->hasMany(SubStepInstallment::class, 'installment_step_id', 'installment_step_id')
                    ->where('user_id', $this->user_id);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeReviewed()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_UNDER_REVIEW]);
    }

    public function getSubStepsSummaryAttribute()
    {
        if (!$this->sub_steps_data) {
            return null;
        }

        return collect($this->sub_steps_data)->map(function ($step, $index) {
            return "Part " . ($index + 1) . ": ₹" . number_format($step['amount'], 2) . " (Due: " . date('Y-m-d', $step['deadline']) . ")";
        })->implode(', ');
    }

    public function generateSubStepsData($numberOfSubSteps = 2, $splitRatio = [50, 50])
    {
        $subSteps = [];
        $totalAmount = $this->original_amount;
        $baseDeadline = $this->original_deadline;
        
        for ($i = 0; $i < $numberOfSubSteps; $i++) {
            $subSteps[] = [
                'amount' => $totalAmount * ($splitRatio[$i] / 100),
                'deadline' => $baseDeadline + ($i * 30 * 24 * 60 * 60), // 30 days apart
                'order' => $i + 1,
                'status' => 'pending'
            ];
        }

        $this->sub_steps_data = $subSteps;
        $this->number_of_sub_steps = $numberOfSubSteps;
        
        return $subSteps;
    }

    /**
     * Automatically create sub-steps for unpaid installment steps
     */
    // public static function createAutomaticSubSteps($userId, $installmentOrderId)
    // {
    //     try {
    //         $order = \App\Models\InstallmentOrder::with(['installment.steps'])->find($installmentOrderId);
            
    //         if (!$order) {
    //             return false;
    //         }

    //         $createdSubSteps = [];
            
    //         foreach ($order->installment->steps as $step) {
    //             // Check if this step is unpaid
    //             $stepPayment = \App\Models\InstallmentOrderPayment::where('installment_order_id', $installmentOrderId)
    //                 ->where('step_id', $step->id)
    //                 ->where('status', 'paid')
    //                 ->first();

    //                  $adminapproved = NewSupportForAsttrolok::where('user_id', $userId)
    //                      ->where('status', 'approved')
    //                      ->where('webinar_id', $order->webinar_id)
    //                     ->count();
    //                 print_r($stepPayment);die;

    //             if (!$stepPayment) {
    //                 // Check if sub-steps already exist for this step
    //                 $existingSubSteps = SubStepInstallment::where('user_id', $userId)
    //                     ->where('installment_step_id', $step->id)
    //                     ->count();

    //                 if ($existingSubSteps == 0) {
    //                     // Calculate step amount
    //                     $webinar = \App\Models\Webinar::find($order->webinar_id);
    //                     $webinarPrice = $webinar->price ?? 0;
                        
    //                     if ($step->amount_type == 'percent') {
    //                         $totalStepAmount = ($webinarPrice * $step->amount) / 100;
    //                     } else {
    //                         $totalStepAmount = $step->amount;
    //                     }

    //                     // Create 2 sub-steps (50-50 split)
    //                     $subStep1Amount = $totalStepAmount / 2;
    //                     $subStep2Amount = $totalStepAmount / 2;

    //                     // Calculate deadline
    //                     $dueAt = ($step->deadline * 86400) + $order->created_at;

    //                     // Create Sub-Step 1
    //                     $subStep1 = SubStepInstallment::create([
    //                         'user_id' => $userId,
    //                         'webinar_id' => $order->webinar_id,
    //                         'installment_step_id' => $step->id,
    //                         'sub_step_number' => 1,
    //                         'price' => $subStep1Amount,
    //                         'due_date' => $dueAt,
    //                         'status' => SubStepInstallment::STATUS_APPROVED,
    //                     ]);

    //                     // Create Sub-Step 2 (30 days later)
    //                     $subStep2 = SubStepInstallment::create([
    //                         'user_id' => $userId,
    //                         'webinar_id' => $order->webinar_id,
    //                         'installment_step_id' => $step->id,
    //                         'sub_step_number' => 2,
    //                         'price' => $subStep2Amount,
    //                         'due_date' => $dueAt + (30 * 24 * 60 * 60), // 30 days later
    //                         'status' => SubStepInstallment::STATUS_APPROVED,
    //                     ]);

    //                     $createdSubSteps[] = [
    //                         'step_id' => $step->id,
    //                         'step_title' => $step->title,
    //                         'sub_step_1' => $subStep1,
    //                         'sub_step_2' => $subStep2,
    //                     ];

    //                     \Log::info('Automatic sub-steps created', [
    //                         'user_id' => $userId,
    //                         'installment_order_id' => $installmentOrderId,
    //                         'step_id' => $step->id,
    //                         'sub_step_1_id' => $subStep1->id,
    //                         'sub_step_2_id' => $subStep2->id,
    //                         'amount_split' => [$subStep1Amount, $subStep2Amount]
    //                     ]);
    //                 }
    //             }
    //         }

    //         \Log::info('=== AUTOMATIC SUB-STEPS CREATION COMPLETED ===', [
    //             'user_id' => $userId,
    //             'installment_order_id' => $installmentOrderId,
    //             'total_steps_processed' => count($createdSubSteps),
    //             'created_sub_steps' => $createdSubSteps
    //         ]);

    //         return $createdSubSteps;

    //     } catch (\Exception $e) {
    //         \Log::error('Error creating automatic sub-steps: ' . $e->getMessage(), [
    //             'user_id' => $userId,
    //             'installment_order_id' => $installmentOrderId,
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine()
    //         ]);
            
    //         return false;
    //     }
    // }

    public static function createAutomaticSubSteps($userId, $installmentOrderId)
{
    try {
        $order = \App\Models\InstallmentOrder::with(['installment.steps'])->find($installmentOrderId);
        
        if (!$order) {
            return false;
        }

        $createdSubSteps = [];
        $steps = $order->installment->steps->sortBy('deadline'); // Sort by deadline to maintain order
        
        // Find the first unpaid step that should have sub-steps created
        $shouldCreateSubSteps = false;
        $paidStepsCount = 0;
        
        foreach ($steps as $index => $step) {
            // Check if this step is paid
            $stepPayment = \App\Models\InstallmentOrderPayment::where('installment_order_id', $installmentOrderId)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->first();

            // Check admin approval
            $adminApproved = NewSupportForAsttrolok::where('user_id', $userId)
                ->where('status', 'approved')
                ->where('webinar_id', $order->webinar_id)
                ->exists();

            if ($stepPayment) {
                // This step is paid, increment counter
                $paidStepsCount++;
                continue;
            }

            // Step is unpaid - check if we should create sub-steps
            // Logic: Create sub-steps only for the immediate next unpaid step after all paid steps
            if ($paidStepsCount == $index && $adminApproved) {
                // This is the next unpaid step in sequence and admin is approved
                
                // Check if sub-steps already exist for this step
                $existingSubSteps = SubStepInstallment::where('user_id', $userId)
                    ->where('installment_step_id', $step->id)
                    ->count();

                if ($existingSubSteps == 0) {
                    // Calculate step amount
                    $webinar = \App\Models\Webinar::find($order->webinar_id);
                    $webinarPrice = $webinar->price ?? 0;
                    
                    if ($step->amount_type == 'percent') {
                        $totalStepAmount = ($webinarPrice * $step->amount) / 100;
                    } else {
                        $totalStepAmount = $step->amount;
                    }

                    // Create 2 sub-steps (50-50 split)
                    $subStep1Amount = $totalStepAmount / 2;
                    $subStep2Amount = $totalStepAmount / 2;

                    // Calculate deadline
                    $dueAt = ($step->deadline * 86400) + $order->created_at;

                    // Create Sub-Step 1
                    $subStep1 = SubStepInstallment::create([
                        'user_id' => $userId,
                        'webinar_id' => $order->webinar_id,
                        'installment_step_id' => $step->id,
                        'sub_step_number' => 1,
                        'price' => $subStep1Amount,
                        'due_date' => $dueAt,
                        'status' => SubStepInstallment::STATUS_APPROVED,
                    ]);

                    // Create Sub-Step 2 (30 days later)
                    $subStep2 = SubStepInstallment::create([
                        'user_id' => $userId,
                        'webinar_id' => $order->webinar_id,
                        'installment_step_id' => $step->id,
                        'sub_step_number' => 2,
                        'price' => $subStep2Amount,
                        'due_date' => $dueAt + (30 * 24 * 60 * 60), // 30 days later
                        'status' => SubStepInstallment::STATUS_APPROVED,
                    ]);

                    $createdSubSteps[] = [
                        'step_id' => $step->id,
                        'step_number' => $index + 1,
                        'step_title' => $step->title,
                        'sub_step_1' => $subStep1,
                        'sub_step_2' => $subStep2,
                    ];

                    \Log::info('Automatic sub-steps created for next unpaid installment', [
                        'user_id' => $userId,
                        'installment_order_id' => $installmentOrderId,
                        'step_id' => $step->id,
                        'step_number' => $index + 1,
                        'paid_steps_before' => $paidStepsCount,
                        'sub_step_1_id' => $subStep1->id,
                        'sub_step_2_id' => $subStep2->id,
                        'amount_split' => [$subStep1Amount, $subStep2Amount]
                    ]);
                }
                
                // Only create sub-steps for one unpaid step at a time, then break
                break;
            } else {
                // Skip this and remaining steps - not in sequence yet
                \Log::info('Skipping step - waiting for previous steps to be paid', [
                    'user_id' => $userId,
                    'installment_order_id' => $installmentOrderId,
                    'step_id' => $step->id,
                    'step_number' => $index + 1,
                    'paid_steps_count' => $paidStepsCount,
                    'admin_approved' => $adminApproved
                ]);
                break;
            }
        }

        \Log::info('=== AUTOMATIC SUB-STEPS CREATION COMPLETED ===', [
            'user_id' => $userId,
            'installment_order_id' => $installmentOrderId,
            'total_paid_steps' => $paidStepsCount,
            'created_sub_steps' => $createdSubSteps
        ]);

        return $createdSubSteps;

    } catch (\Exception $e) {
        \Log::error('Error creating automatic sub-steps: ' . $e->getMessage(), [
            'user_id' => $userId,
            'installment_order_id' => $installmentOrderId,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return false;
    }
}
}
