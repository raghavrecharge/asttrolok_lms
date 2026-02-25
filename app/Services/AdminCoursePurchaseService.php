<?php

namespace App\Services;

use App\Models\Webinar;
use App\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sale;
use App\Models\Discount;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Accounting;

class AdminCoursePurchaseService
{
    /**
     * Purchase course directly (full payment).
     * P2 FIX: Uses DB::transaction() closure for proper savepoint support
     * when called inside an already-open transaction.
     */
    public function purchaseCourseDirectly($courseId, $userId, $discountId = null, $adminId = null)
    {
        try {
            $result = DB::transaction(function () use ($courseId, $userId, $discountId, $adminId) {
                $course = Webinar::findOrFail($courseId);
                $user = User::findOrFail($userId);
                
                // Calculate price with discount
                $originalPrice = $course->getPrice();
                $discountAmount = 0;
                $finalPrice = $originalPrice;
                
                if ($discountId) {
                    $discount = Discount::find($discountId);
                    if ($discount && $discount->checkValidDiscount() == 'ok') {
                        $discountAmount = $this->calculateDiscount($discount, $course, $originalPrice);
                        $finalPrice = $originalPrice - $discountAmount;
                    }
                }
                
                // Create order
                $order = Order::create([
                    'user_id' => $userId,
                    'status' => Order::$paid,
                    'payment_method' => 'credit',
                    'is_charge_account' => 0,
                    'amount' => $originalPrice,
                    'tax' => 0,
                    'total_discount' => $discountAmount,
                    'total_amount' => $finalPrice,
                    'product_delivery_fee' => null,
                    'reference_id' => null,
                    'payment_data' => json_encode([
                        'admin_id' => $adminId,
                        'purchase_type' => 'admin_direct',
                        'original_price' => $originalPrice,
                        'discount_amount' => $discountAmount
                    ]),
                    'created_at' => time(),
                ]);
                
                // Create order item
                OrderItem::create([
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'webinar_id' => $courseId,
                    'amount' => $finalPrice,
                    'total_amount' => $finalPrice,
                    'discount' => $discountAmount,
                    'created_at' => time(),
                ]);
                
                // Create sale record
                $sale = Sale::create([
                    'buyer_id' => $userId,
                    'seller_id' => $course->creator_id,
                    'order_id' => $order->id,
                    'webinar_id' => $courseId,
                    'type' => Order::$webinar,
                    'payment_method' => 'credit',
                    'amount' => $finalPrice,
                    'tax' => 0,
                    'commission' => 0,
                    'discount' => $discountAmount,
                    'total_amount' => $finalPrice,
                    'created_at' => time(),
                ]);
                
                // Create accounting record
                Accounting::create([
                    'user_id' => $userId,
                    'webinar_id' => $courseId,
                    'amount' => $finalPrice,
                    'type' => Accounting::$addiction,
                    'description' => "Admin Direct Purchase: {$course->title}",
                    'created_at' => time(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Course purchased successfully',
                    'order_id' => $order->id,
                    'sale_id' => $sale->id,
                    'amount' => $finalPrice,
                    'course' => $course,
                    'user' => $user
                ];
            });
            
            Log::info('Admin direct course purchase completed', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'admin_id' => $adminId,
                'order_id' => $result['order_id'],
                'amount' => $result['amount']
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Admin direct course purchase failed', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Purchase failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Purchase course with installment plan.
     * P2 FIX: Uses DB::transaction() closure for proper savepoint support.
     */
    public function purchaseCourseWithInstallment($courseId, $userId, $installmentId, $discountId = null, $adminId = null)
    {
        try {
            $result = DB::transaction(function () use ($courseId, $userId, $installmentId, $discountId, $adminId) {
            $course = Webinar::findOrFail($courseId);
            $user = User::findOrFail($userId);
            $installment = Installment::findOrFail($installmentId);
            
            // Calculate price with discount
            $originalPrice = $course->getPrice();
            $discountAmount = 0;
            $finalPrice = $originalPrice;
            
            if ($discountId) {
                $discount = Discount::find($discountId);
                if ($discount && $discount->checkValidDiscount() == 'ok') {
                    $discountAmount = $this->calculateDiscount($discount, $course, $originalPrice);
                    $finalPrice = $originalPrice - $discountAmount;
                }
            }
            
            // Create installment order
            $installmentOrder = InstallmentOrder::create([
                'installment_id' => $installmentId,
                'user_id' => $userId,
                'webinar_id' => $courseId,
                'discount' => $discountAmount,
                'item_price' => $finalPrice,
                'status' => 'paying',
                'created_at' => time(),
            ]);
            
            // Create upfront payment
            $upfrontAmount = $installment->getUpfront($finalPrice);
            $upfrontPayment = InstallmentOrderPayment::create([
                'installment_order_id' => $installmentOrder->id,
                'type' => 'upfront',
                'step_id' => null,
                'amount' => $upfrontAmount,
                'status' => 'paid',
                'created_at' => time(),
            ]);
            
            // Create main order for upfront
            $order = Order::create([
                'user_id' => $userId,
                'status' => Order::$paid,
                'payment_method' => 'credit',
                'is_charge_account' => 0,
                'amount' => $upfrontAmount,
                'tax' => 0,
                'total_discount' => $discountAmount,
                'total_amount' => $upfrontAmount,
                'product_delivery_fee' => null,
                'reference_id' => null,
                'payment_data' => json_encode([
                    'admin_id' => $adminId,
                    'purchase_type' => 'admin_installment',
                    'installment_order_id' => $installmentOrder->id,
                    'original_price' => $originalPrice,
                    'discount_amount' => $discountAmount,
                    'upfront_amount' => $upfrontAmount
                ]),
                'created_at' => time(),
            ]);
            
            // Create order item
            $orderItem = OrderItem::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'webinar_id' => $courseId,
                'installment_payment_id' => $upfrontPayment->id,
                'installment_type' => 'part',
                'amount' => $upfrontAmount,
                'total_amount' => $upfrontAmount,
                'discount' => $discountAmount,
                'created_at' => time(),
            ]);
            
            // Create sale record for upfront
            $sale = Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $course->creator_id,
                'order_id' => $order->id,
                'webinar_id' => $courseId,
                'installment_payment_id' => $upfrontPayment->id,
                'type' => Order::$installmentPayment,
                'payment_method' => 'credit',
                'amount' => $upfrontAmount,
                'tax' => 0,
                'commission' => 0,
                'discount' => $discountAmount,
                'total_amount' => $upfrontAmount,
                'status' => 'part',
                'created_at' => time(),
            ]);
            
            // Create accounting record
            Accounting::create([
                'user_id' => $userId,
                'webinar_id' => $courseId,
                'amount' => $upfrontAmount,
                'type' => Accounting::$addiction,
                'description' => "Admin Installment Purchase (Upfront): {$course->title}",
                'created_at' => time(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Course purchased with installment successfully',
                'order_id' => $order->id,
                'sale_id' => $sale->id,
                'installment_order_id' => $installmentOrder->id,
                'upfront_payment_id' => $upfrontPayment->id,
                'amount' => $upfrontAmount,
                'total_amount' => $finalPrice,
                'course' => $course,
                'user' => $user,
                'installment' => $installment
            ];
            }); // end DB::transaction
            
            Log::info('Admin installment course purchase completed', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'admin_id' => $adminId,
                'installment_id' => $installmentId,
                'order_id' => $result['order_id'],
                'installment_order_id' => $result['installment_order_id'],
                'upfront_amount' => $result['amount']
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Admin installment course purchase failed', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'installment_id' => $installmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Installment purchase failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get available installment plans for a course
     */
    public function getAvailableInstallmentPlans($courseId)
    {
        try {
            $course = Webinar::findOrFail($courseId);
            
            $installmentPlans = new \App\Mixins\Installment\InstallmentPlans();
            $installments = $installmentPlans->getPlans(
                'courses',
                $course->id,
                $course->type,
                $course->category_id,
                $course->teacher_id
            );
            $installments->loadCount('steps');
            
            return [
                'success' => true,
                'installments' => $installments,
                'course' => $course
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get installment plans', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get installment plans: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate discount amount
     */
    private function calculateDiscount($discount, $course, $originalPrice)
    {
        $discountAmount = 0;
        
        if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
            $discountAmount = ($originalPrice > $discount->amount) ? $discount->amount : $originalPrice;
        } else {
            $percent = $discount->percent ?? 0;
            $discountAmount = ($originalPrice > 0) ? $originalPrice * $percent / 100 : 0;
        }
        
        // Apply max amount limit if set
        if ($discount->discount_type != Discount::$discountTypeFixedAmount && 
            !empty($discount->max_amount) && 
            $discountAmount > $discount->max_amount) {
            $discountAmount = $discount->max_amount;
        }
        
        return $discountAmount;
    }
    
    /**
     * Validate purchase request
     */
    public function validatePurchaseRequest($courseId, $userId, $purchaseType = 'direct', $installmentId = null)
    {
        try {
            $course = Webinar::findOrFail($courseId);
            $user = User::findOrFail($userId);
            
            // Check if course is active
            if ($course->status != Webinar::$active) {
                return [
                    'valid' => false,
                    'message' => 'Course is not active for purchase'
                ];
            }
            
            // Check if user already purchased
            $existingSale = Sale::where('buyer_id', $userId)
                                ->where('webinar_id', $courseId)
                                ->whereNull('refund_at')
                                ->first();
            
            if ($existingSale) {
                return [
                    'valid' => false,
                    'message' => 'User already purchased this course'
                ];
            }
            
            // Validate installment if purchase type is installment
            if ($purchaseType == 'installment') {
                if (!$installmentId) {
                    return [
                        'valid' => false,
                        'message' => 'Installment ID is required for installment purchase'
                    ];
                }
                
                $installment = Installment::find($installmentId);
                if (!$installment || !$installment->enable) {
                    return [
                        'valid' => false,
                        'message' => 'Invalid or disabled installment plan'
                    ];
                }
            }
            
            return [
                'valid' => true,
                'message' => 'Purchase request is valid',
                'course' => $course,
                'user' => $user
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get purchase summary before processing
     */
    public function getPurchaseSummary($courseId, $userId, $purchaseType = 'direct', $installmentId = null, $discountId = null)
    {
        try {
            $course = Webinar::findOrFail($courseId);
            $originalPrice = $course->getPrice();
            
            $discountAmount = 0;
            if ($discountId) {
                $discount = Discount::find($discountId);
                if ($discount && $discount->checkValidDiscount() == 'ok') {
                    $discountAmount = $this->calculateDiscount($discount, $course, $originalPrice);
                }
            }
            
            $finalPrice = $originalPrice - $discountAmount;
            
            $summary = [
                'course' => $course,
                'original_price' => $originalPrice,
                'discount_amount' => $discountAmount,
                'final_price' => $finalPrice,
                'purchase_type' => $purchaseType
            ];
            
            if ($purchaseType == 'installment' && $installmentId) {
                $installment = Installment::findOrFail($installmentId);
                $upfrontAmount = $installment->getUpfront($finalPrice);
                $remainingAmount = $finalPrice - $upfrontAmount;
                
                $summary['installment'] = $installment;
                $summary['upfront_amount'] = $upfrontAmount;
                $summary['remaining_amount'] = $remainingAmount;
                $summary['total_installments'] = $installment->steps_count;
            }
            
            return [
                'success' => true,
                'summary' => $summary
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get purchase summary: ' . $e->getMessage()
            ];
        }
    }
}
