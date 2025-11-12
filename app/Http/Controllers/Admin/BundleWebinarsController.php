<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\BundleWebinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BundleWebinarsController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('admin_bundles_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'bundle_id' => 'required',
            'webinar_id' => 'required|exists:webinars,id',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $bundle = Bundle::find($data['bundle_id']);

        if (!empty($bundle)) {
            $bundleWebinar = BundleWebinar::where('bundle_id', $data['bundle_id'])
                ->where('webinar_id', $data['webinar_id'])
                ->first();

            if (!empty($bundleWebinar)) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'webinar_id' => [trans('update.this_course_has_already_been_selected_for_this_bundle')]
                    ],
                ], 422);
            }

            BundleWebinar::create([
                'creator_id' => $bundle->creator_id,
                'bundle_id' => $data['bundle_id'],
                'webinar_id' => $data['webinar_id'],
            ]);
        }

        return response()->json([
            'code' => 200,
        ], 200);
    }
    
    public function storeConsultation(Request $request)
    {
        // die('mayank');
        $this->authorize('admin_bundles_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'bundle_id' => 'required',
             'consultation_type' => 'required|in:specific,range,all',
    'consultant_id' => 'required_if:consultation_type,specific',
    'starting_price' => 'required_if:consultation_type,range|nullable|numeric|min:0',
    'ending_price' => 'required_if:consultation_type,range|nullable|numeric|gte:starting_price',
    
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $bundle = Bundle::find($data['bundle_id']);

        if (!empty($bundle)) {
            $bundleWebinar = BundleWebinar::where('bundle_id', $data['bundle_id'])
                ->where('consultation_type', $data['consultation_type'])
                ->first();

            if (!empty($bundleWebinar)) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'consultation_id' => [trans('update.this_consultation_has_already_been_selected_for_this_bundle')]
                    ],
                ], 422);
            }

            BundleWebinar::create([
                'creator_id' => $bundle->creator_id,
                'bundle_id' => $data['bundle_id'],
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
    }
    
    public function storeProduct(Request $request)
    {
        $this->authorize('admin_bundles_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'bundle_id' => 'required',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $bundle = Bundle::find($data['bundle_id']);

        if (!empty($bundle)) {
            $bundleWebinar = BundleWebinar::where('bundle_id', $data['bundle_id'])
                ->where('product_id', $data['product_id'])
                ->first();

            if (!empty($bundleWebinar)) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'product_id' => [trans('update.this_course_has_already_been_selected_for_this_bundle')]
                    ],
                ], 422);
            }

            BundleWebinar::create([
                'creator_id' => $bundle->creator_id,
                'bundle_id' => $data['bundle_id'],
                'product_id' => $data['product_id'],
            ]);
        }

        return response()->json([
            'code' => 200,
        ], 200);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        if (!empty($request->get('item_id'))) {
            $bundleWebinar = BundleWebinar::where('id', $id)
                ->first();

            if (!empty($bundleWebinar)) {
                return response()->json([
                    'bundleWebinar' => $bundleWebinar
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'bundle_id' => 'required',
            'webinar_id' => 'required|exists:webinars,id',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $bundleWebinar = BundleWebinar::where('id', $id)
            ->where('bundle_id', $data['bundle_id'])
            ->first();

        if (!empty($bundleWebinar)) {

            $checkBundleWebinar = BundleWebinar::where('id', '!=', $id)
                ->where('bundle_id', $data['bundle_id'])
                ->where('webinar_id', $data['webinar_id'])
                ->first();

            if (!empty($checkBundleWebinar)) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'webinar_id' => [trans('update.this_course_has_already_been_selected_for_this_bundle')]
                    ],
                ], 422);
            }


            $bundleWebinar->update([
                'bundle_id' => $data['bundle_id'],
                'webinar_id' => $data['webinar_id'],
            ]);
        }

        return response()->json([
            'code' => 200,
        ], 200);
    }

    public function updateConsultation(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'bundle_id' => 'required',
            'consultation_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $bundleWebinar = BundleWebinar::where('id', $id)
            ->where('bundle_id', $data['bundle_id'])
            ->first();

        if (!empty($bundleWebinar)) {

            $checkBundleWebinar = BundleWebinar::where('id', '!=', $id)
                ->where('bundle_id', $data['bundle_id'])
                ->where('consultation_type', $data['consultation_type'])
                ->first();
            if (!empty($checkBundleWebinar)) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'consultation_id' => [trans('update.this_consultation_has_already_been_selected_for_this_bundle')]
                    ],
                ], 422);
            }


            $bundleWebinar->update([
                'bundle_id' => $data['bundle_id'],
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
    }

    public function updateProduct(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'bundle_id' => 'required',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $bundleWebinar = BundleWebinar::where('id', $id)
            ->where('bundle_id', $data['bundle_id'])
            ->first();

        if (!empty($bundleWebinar)) {

            $checkBundleWebinar = BundleWebinar::where('id', '!=', $id)
                ->where('bundle_id', $data['bundle_id'])
                ->where('product_id', $data['product_id'])
                ->first();

            if (!empty($checkBundleWebinar)) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'product_id' => [trans('update.this_product_has_already_been_selected_for_this_bundle')]
                    ],
                ], 422);
            }


            $bundleWebinar->update([
                'bundle_id' => $data['bundle_id'],
                'product_id' => $data['product_id'],
            ]);
        }

        return response()->json([
            'code' => 200,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        BundleWebinar::find($id)->delete();

        return redirect()->back();
    }
}
