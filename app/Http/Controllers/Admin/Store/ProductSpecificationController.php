<?php

namespace App\Http\Controllers\Admin\Store;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSelectedSpecification;
use App\Models\ProductSelectedSpecificationMultiValue;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationCategory;
use App\Models\ProductSpecificationMultiValue;
use App\Models\Translation\ProductSelectedSpecificationTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductSpecificationController extends Controller
{
    public function getItem($id)
    {
        try {
            $this->authorize('admin_store_edit_product');

            $specification = ProductSpecification::where('id', $id)
                ->first();

            if (!empty($specification)) {
                $multiValues = ProductSpecificationMultiValue::where('specification_id', $specification->id)->get();

                $data = [
                    'specification' => $specification,
                    'multiValues' => $multiValues
                ];

                return response()->json($data);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('getItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('admin_store_edit_product');

            $data = $request->get('ajax')['new'];

            $rules = [
                'product_id' => 'required',
                'input_type' => 'required|in:' . implode(',', ProductSpecification::$inputTypes),
                'specification_id' => 'required|exists:product_specifications,id',
                'multi_values' => 'required_if:input_type,multi_value',
                'summary' => 'required_if:input_type,textarea',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = Product::where('id', $data['product_id'])
                ->first();

            if (!empty($product)) {

                $selectedSpecification = ProductSelectedSpecification::create([
                    'creator_id' => $product->creator_id,
                    'product_id' => $product->id,
                    'product_specification_id' => $data['specification_id'],
                    'type' => $data['input_type'],
                    'allow_selection' => (!empty($data['allow_selection']) and $data['allow_selection'] == 'on'),
                    'order' => null,
                    'status' => (!empty($data['status']) and $data['status'] == 'on') ? ProductSelectedSpecification::$Active : ProductSelectedSpecification::$Inactive,
                    'created_at' => time(),
                ]);

                if (!empty($selectedSpecification)) {
                    if ($data['input_type'] == 'multi_value') {

                        $this->handleSelectedSpecificationMultiValue($selectedSpecification, $data['multi_values']);

                    } else if (!empty($data['summary'])) {
                        ProductSelectedSpecificationTranslation::updateOrCreate([
                            'locale' => mb_strtolower($data['locale']),
                            'product_selected_specification_id' => $selectedSpecification->id
                        ], [
                            'value' => $data['summary']
                        ]);
                    }
                }

                return response()->json([
                    'code' => 200,
                ], 200);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleSelectedSpecificationMultiValue($selectedSpecification, $multiValues)
    {
        ProductSelectedSpecificationMultiValue::where('selected_specification_id', $selectedSpecification->id)->delete();

        if (!empty($multiValues) and !is_array($multiValues)) {
            $multiValues = [$multiValues];
        }

        if (!empty($multiValues) and is_array($multiValues)) {
            foreach ($multiValues as $multiValue) {
                ProductSelectedSpecificationMultiValue::create([
                    'selected_specification_id' => $selectedSpecification->id,
                    'specification_multi_value_id' => $multiValue
                ]);
            }
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin_store_edit_product');

            $data = $request->get('ajax')[$id];

            $rules = [
                'product_id' => 'required',
                'input_type' => 'required|in:' . implode(',', ProductSpecification::$inputTypes),
                'specification_id' => 'required|exists:product_specifications,id',
                'multi_values' => 'required_if:input_type,multi_value',
                'summary' => 'required_if:input_type,textarea',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = Product::where('id', $data['product_id'])
                ->first();

            if (!empty($product)) {
                $selectedSpecification = ProductSelectedSpecification::where('product_id', $product->id)
                    ->where('id', $id)
                    ->first();

                if (!empty($selectedSpecification)) {

                    $selectedSpecification->update([

                        'type' => $data['input_type'],
                        'allow_selection' => (!empty($data['allow_selection']) and $data['allow_selection'] == 'on'),
                        'status' => (!empty($data['status']) and $data['status'] == 'on') ? ProductSelectedSpecification::$Active : ProductSelectedSpecification::$Inactive,
                        'created_at' => time(),
                    ]);

                    if ($data['input_type'] == 'multi_value') {

                        $this->handleSelectedSpecificationMultiValue($selectedSpecification, $data['multi_values']);

                    } else if (!empty($data['summary'])) {
                        ProductSelectedSpecificationTranslation::updateOrCreate([
                            'locale' => mb_strtolower($data['locale']),
                            'product_selected_specification_id' => $selectedSpecification->id
                        ], [
                            'value' => $data['summary']
                        ]);
                    }

                    return response()->json([
                        'code' => 200,
                    ], 200);
                }
            }
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
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
            $this->authorize('admin_store_edit_product');

            $selectedSpecification = ProductSelectedSpecification::where('id', $id)
                ->first();

            if (!empty($selectedSpecification)) {
                $selectedSpecification->delete();
            }

            return back();
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function search(Request $request)
    {
        try {
            $term = $request->get('term');
            $categoryId = $request->get('category_id');

            if (!empty($term)) {
                $specificationIds = ProductSpecificationCategory::where('category_id', $categoryId)
                    ->pluck('specification_id')
                    ->toArray();

                $specifications = ProductSpecification::whereIn('id', $specificationIds)
                    ->whereTranslationLike('title', '%' . $term . '%')
                    ->get();

                return response()->json($specifications);
            }

            return response()->json([]);
        } catch (\Exception $e) {
            \Log::error('search error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getByCategoryId($categoryId)
    {
        try {
            $defaultLocale = getDefaultLocale();

            $specificationIds = ProductSpecificationCategory::where('category_id', $categoryId)
                ->pluck('specification_id')
                ->toArray();

            $specifications = ProductSpecification::select('*')
                ->whereIn('id', $specificationIds)
                ->get();

            return response()->json([
                'specifications' => $specifications,
                'defaultLocale' => mb_strtolower($defaultLocale)
            ], 200);
        } catch (\Exception $e) {
            \Log::error('getByCategoryId error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
