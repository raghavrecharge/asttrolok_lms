<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\InstallmentStep;
use App\Models\Order;
use App\Models\Webinar;

class SubStepInstallment extends Model
{
    protected $table = 'sub_step_installments';

    protected $fillable = [
        'installment_step_id',
        'user_id',
        'order_id',
        'webinar_id',
        'sub_step_number',
        'price',
        'due_date',
        'status',
        'payment_date',
        'transaction_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'due_date' => 'integer',
        'payment_date' => 'integer',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function installmentStep()
    {
        return $this->belongsTo(InstallmentStep::class, 'installment_step_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class, 'webinar_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByInstallmentStep($query, $stepId)
    {
        return $query->where('installment_step_id', $stepId);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOverdue()
    {
        return $this->status === self::STATUS_PENDING && $this->due_date < time();
    }

    public function markAsPaid($transactionId = null)
    {
        $this->status = self::STATUS_PAID;
        $this->payment_date = time();
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        $this->save();
    }

    public function markAsApproved()
    {
        $this->status = self::STATUS_APPROVED;
        $this->save();
    }

    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    public function getDueDateFormattedAttribute()
    {
        return date('Y-m-d', $this->due_date);
    }

    public function getPaymentDateFormattedAttribute()
    {
        return $this->payment_date ? date('Y-m-d', $this->payment_date) : null;
    }

    public function getSubStepNameAttribute()
    {
        return "Part " . $this->sub_step_number;
    }

    public function getDaysUntilDueAttribute()
    {
        if ($this->isPaid()) {
            return 0;
        }
        
        $daysUntilDue = ceil(($this->due_date - time()) / (24 * 60 * 60));
        return max(0, $daysUntilDue);
    }

    public static function createFromRestructureRequest(InstallmentRestructureRequest $request)
    {
        $subSteps = [];
        
        if (!$request->sub_steps_data) {
            return $subSteps;
        }

        foreach ($request->sub_steps_data as $index => $subStepData) {
            $subStep = self::create([
                'installment_step_id' => $request->installment_step_id,
                'user_id' => $request->user_id,
                'webinar_id' => $request->webinar_id,
                'sub_step_number' => $subStepData['order'] ?? ($index + 1),
                'price' => $subStepData['amount'],
                'due_date' => $subStepData['deadline'],
                'status' => self::STATUS_PENDING,
            ]);
            
            $subSteps[] = $subStep;
        }

        return $subSteps;
    }

    public function checkAllSubStepsPaid($installmentStepId, $userId)
    {
        $totalSubSteps = self::where('installment_step_id', $installmentStepId)
                            ->where('user_id', $userId)
                            ->count();
                            
        $paidSubSteps = self::where('installment_step_id', $installmentStepId)
                           ->where('user_id', $userId)
                           ->where('status', self::STATUS_PAID)
                           ->count();

        return $totalSubSteps > 0 && $totalSubSteps === $paidSubSteps;
    }
}
