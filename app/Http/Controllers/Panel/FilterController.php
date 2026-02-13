<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Filter;

class FilterController extends Controller
{
    public function getByCategoryId($categoryId)
    {
        try {
            $defaultLocale = getDefaultLocale();

            $filters = Filter::select('*')
                ->where('category_id', $categoryId)
                ->with([
                    'options'  => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                ])
                ->get();

            return response()->json([
                'filters' => $filters,
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
