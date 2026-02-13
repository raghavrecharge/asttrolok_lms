<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallmentPaymentSyncController extends Controller
{
    /**
     * Sync installment payments based on WebinarPartPayment
     * This will update InstallmentOrderPayment status based on actual payments made
     */
    public function syncPaymentsFromPartPayments($orderId = null)
    {
        try {
            Log::info('=== Starting Installment Payment Sync ===');
            
            if ($orderId) {
                $orders = collect([InstallmentOrder::find($orderId)]);
            } else {
                $orders = InstallmentOrder::with(['installment.steps'])
                    ->whereIn('status', ['open', 'paying'])
                    ->get();
            }
            
            $updatedCount = 0;
            
            foreach ($orders as $order) {
                if (!$order) continue;
                
                Log::info('Processing order', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'webinar_id' => $order->webinar_id
                ]);
                
                // Get total part payments for this user and webinar
                $totalPartAmount = DB::table('webinar_part_payment')
                    ->where('user_id', $order->user_id)
                    ->where('webinar_id', $order->webinar_id)
                    ->sum('amount');
                
                // Get upfront payment
                $upfrontPayment = InstallmentOrderPayment::where('installment_order_id', $order->id)
                    ->where('type', 'upfront')
                    ->first();
                
                $upfrontAmount = $upfrontPayment ? $upfrontPayment->amount : 0;
                $availableAmount = $totalPartAmount; // Only use part payments for steps
                
                Log::info('Payment amounts', [
                    'upfront' => $upfrontAmount,
                    'part_payments' => $totalPartAmount,
                    'available_for_steps' => $availableAmount
                ]);
                
                // Get steps in order
                $steps = InstallmentStep::where('installment_id', $order->installment_id)
                    ->orderBy('id')
                    ->get();
                
                foreach ($steps as $step) {
                    // Calculate step amount
                    $itemPrice = $order->getItemPrice();
                    if ($step->amount_type == 'percent') {
                        $stepAmount = ($itemPrice * $step->amount) / 100;
                    } else {
                        $stepAmount = $step->amount;
                    }
                    
                    // Find existing payment record
                    $payment = InstallmentOrderPayment::where('installment_order_id', $order->id)
                        ->where('step_id', $step->id)
                        ->first();
                    
                    if (!$payment) {
                        // Create payment record if doesn't exist
                        $payment = InstallmentOrderPayment::create([
                            'installment_order_id' => $order->id,
                            'type' => 'step',
                            'step_id' => $step->id,
                            'amount' => $stepAmount,
                            'status' => 'paying',
                            'created_at' => time(),
                        ]);
                        
                        Log::info('Created new payment record', [
                            'step_id' => $step->id,
                            'amount' => $stepAmount
                        ]);
                    }
                    
                    // Check if step can be marked as paid
                    if ($availableAmount >= $stepAmount && $payment->status !== 'paid') {
                        // Update to paid
                        $payment->update(['status' => 'paid']);
                        $availableAmount -= $stepAmount;
                        $updatedCount++;
                        
                        Log::info('Marked step as paid', [
                            'step_id' => $step->id,
                            'step_amount' => $stepAmount,
                            'remaining_available' => $availableAmount
                        ]);
                    } elseif ($payment->status === 'paid') {
                        // Already paid, deduct from available amount
                        $availableAmount -= $stepAmount;
                        
                        Log::info('Step already paid, deducting from available', [
                            'step_id' => $step->id,
                            'step_amount' => $stepAmount,
                            'remaining_available' => $availableAmount
                        ]);
                    } else {
                        Log::info('Insufficient funds for step', [
                            'step_id' => $step->id,
                            'step_amount' => $stepAmount,
                            'available_amount' => $availableAmount
                        ]);
                    }
                }
            }
            
            Log::info('=== Installment Payment Sync Complete ===', [
                'orders_processed' => $orders->count(),
                'payments_updated' => $updatedCount
            ]);
            
            return [
                'success' => true,
                'message' => "Synced {$updatedCount} installment payments",
                'orders_processed' => $orders->count(),
                'payments_updated' => $updatedCount
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in installment payment sync: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Manual sync for specific order
     */
    public function syncOrder($orderId)
    {
        return $this->syncPaymentsFromPartPayments($orderId);
    }
}
