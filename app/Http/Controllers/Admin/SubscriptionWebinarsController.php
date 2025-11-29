<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionWebinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionWebinarsController extends Controller
{
    public function store(Request $request)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            $data = $request->all();

            $validator = Validator::make($data, [
                'subscription_id' => 'required',
                'webinar_id' => 'required|exists:webinars,id',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subscription = Subscription::find($data['subscription_id']);

            if (!empty($subscription)) {
                $subscriptionWebinar = SubscriptionWebinar::where('subscription_id', $data['subscription_id'])
                    ->where('webinar_id', $data['webinar_id'])
                    ->first();

                if (!empty($subscriptionWebinar)) {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'webinar_id' => [trans('update.this_course_has_already_been_selected_for_this_subscription')]
                        ],
                    ], 422);
                }

                SubscriptionWebinar::create([
                    'creator_id' => $subscription->creator_id,
                    'subscription_id' => $data['subscription_id'],
                    'webinar_id' => $data['webinar_id'],
                ]);
            }

            return response()->json([
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeConsultation(Request $request)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            $data = $request->all();

            $validator = Validator::make($data, [
                'subscription_id' => 'required',
                'consultation_type' => 'required',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subscription = Subscription::find($data['subscription_id']);

            if (!empty($subscription)) {
                $subscriptionWebinar = SubscriptionWebinar::where('subscription_id', $data['subscription_id'])
                    ->where('consultation_type', $data['consultation_type'])
                    ->first();

                if (!empty($subscriptionWebinar)) {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'consultation_id' => [trans('update.this_consultation_has_already_been_selected_for_this_subscription')]
                        ],
                    ], 422);
                }

                SubscriptionWebinar::create([
                    'creator_id' => $subscription->creator_id,
                    'subscription_id' => $data['subscription_id'],
                    'consultation_type' => $data['consultation_type'],
                    'consultant_id' => $data['consultant_id'] ?? null,
                    'slot_time' => $data['slot_time'] ?? null,
                    'starting_price' => $data['starting_price'] ?? null,
                    'ending_price' => $data['ending_price'] ?? null,
                ]);
            }

            return response()->json([
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('storeConsultation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeProduct(Request $request)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            $data = $request->all();

            $validator = Validator::make($data, [
                'subscription_id' => 'required',
                'product_id' => 'required|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subscription = Subscription::find($data['subscription_id']);

            if (!empty($subscription)) {
                $subscriptionWebinar = SubscriptionWebinar::where('subscription_id', $data['subscription_id'])
                    ->where('product_id', $data['product_id'])
                    ->first();

                if (!empty($subscriptionWebinar)) {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'product_id' => [trans('update.this_course_has_already_been_selected_for_this_subscription')]
                        ],
                    ], 422);
                }

                SubscriptionWebinar::create([
                    'creator_id' => $subscription->creator_id,
                    'subscription_id' => $data['subscription_id'],
                    'product_id' => $data['product_id'],
                ]);
            }

            return response()->json([
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('storeProduct error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            if (!empty($request->get('item_id'))) {
                $subscriptionWebinar = SubscriptionWebinar::where('id', $id)
                    ->first();

                if (!empty($subscriptionWebinar)) {
                    return response()->json([
                        'subscriptionWebinar' => $subscriptionWebinar
                    ], 200);
                }
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            $data = $request->all();

            $validator = Validator::make($data, [
                'subscription_id' => 'required',
                'webinar_id' => 'required|exists:webinars,id',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subscriptionWebinar = SubscriptionWebinar::where('id', $id)
                ->where('subscription_id', $data['subscription_id'])
                ->first();

            if (!empty($subscriptionWebinar)) {

                $checkSubscriptionWebinar = SubscriptionWebinar::where('id', '!=', $id)
                    ->where('subscription_id', $data['subscription_id'])
                    ->where('webinar_id', $data['webinar_id'])
                    ->first();

                if (!empty($checkSubscriptionWebinar)) {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'webinar_id' => [trans('update.this_course_has_already_been_selected_for_this_subscription')]
                        ],
                    ], 422);
                }

                $subscriptionWebinar->update([
                    'subscription_id' => $data['subscription_id'],
                    'webinar_id' => $data['webinar_id'],
                ]);
            }

            return response()->json([
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function updateConsultation(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            $data = $request->all();

            $validator = Validator::make($data, [
                'subscription_id' => 'required',
                'consultation_type' => 'required',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subscriptionWebinar = SubscriptionWebinar::where('id', $id)
                ->where('subscription_id', $data['subscription_id'])
                ->first();

            if (!empty($subscriptionWebinar)) {

                $checkSubscriptionWebinar = SubscriptionWebinar::where('id', '!=', $id)
                    ->where('subscription_id', $data['subscription_id'])
                    ->where('consultation_type', $data['consultation_type'])
                    ->first();
                if (!empty($checkSubscriptionWebinar)) {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'consultation_id' => [trans('update.this_consultation_has_already_been_selected_for_this_subscription')]
                        ],
                    ], 422);
                }

                $subscriptionWebinar->update([
                    'subscription_id' => $data['subscription_id'],
                    'consultation_type' => $data['consultation_type'],
                    'consultant_id'     => ($data['consultation_type'] === 'specific') ? ($data['consultant_id'] ?? null) : null,
                    'slot_time' => $data['slot_time'] ?? null,
                    'starting_price' => $data['starting_price'] ?? null,
                    'ending_price' => $data['ending_price'] ?? null,
                ]);
            }

            return response()->json([
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('updateConsultation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function updateProduct(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            $data = $request->all();

            $validator = Validator::make($data, [
                'subscription_id' => 'required',
                'product_id' => 'required|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subscriptionWebinar = SubscriptionWebinar::where('id', $id)
                ->where('subscription_id', $data['subscription_id'])
                ->first();

            if (!empty($subscriptionWebinar)) {

                $checkSubscriptionWebinar = SubscriptionWebinar::where('id', '!=', $id)
                    ->where('subscription_id', $data['subscription_id'])
                    ->where('product_id', $data['product_id'])
                    ->first();

                if (!empty($checkSubscriptionWebinar)) {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'product_id' => [trans('update.this_product_has_already_been_selected_for_this_subscription')]
                        ],
                    ], 422);
                }

                $subscriptionWebinar->update([
                    'subscription_id' => $data['subscription_id'],
                    'product_id' => $data['product_id'],
                ]);
            }

            return response()->json([
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('updateProduct error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            SubscriptionWebinar::find($id)->delete();

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
