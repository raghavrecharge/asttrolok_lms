<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Favorite;

class favoriteController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();

            $favorites = Favorite::where('user_id', $user->id)
                ->with(['webinar' => function ($query) {
                    $query->with(['teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    }, 'category']);
                }])
                ->orderBy('created_at','desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('panel.favorites'),
                'favorites' => $favorites
            ];

            return view(getTemplate() . '.panel.webinar.favorites', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $user = auth()->user();

            $favorite = favorite::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($favorite)) {
                $favorite->delete();

                return response()->json([
                    'code' => 200
                ], 200);
            }

            return response()->json([], 422);
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
