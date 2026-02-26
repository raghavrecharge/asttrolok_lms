<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request; // Added this line

use App\Http\Controllers\Controller;
use App\Models\Favorite;

class favoriteController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $user = auth()->user();
            
            $search = $request->get('search');
            $categoryId = $request->get('category_id');
            $instructorId = $request->get('instructor_id');

            $query = Favorite::where('user_id', $user->id);

            $query->whereHas('webinar', function ($qu) use ($search, $categoryId, $instructorId) {
                if (!empty($search)) {
                    $qu->whereTranslationLike('title', '%' . $search . '%');
                }

                if (!empty($categoryId) and $categoryId !== 'all') {
                    $qu->where('category_id', $categoryId);
                }

                if (!empty($instructorId) and $instructorId !== 'all') {
                    $qu->where('teacher_id', $instructorId);
                }

                $qu->where('status', 'active');
            });

            $favorites = $query->with(['webinar' => function ($query) {
                    $query->with(['teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    }, 'category']);
                }])
                ->orderBy('created_at','desc')
                ->paginate(10);

            $categories = \App\Models\Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $instructors = \App\User::where('role_name', \App\Models\Role::$teacher)
                ->select('id', 'full_name')
                ->get();

            $data = [
                'pageTitle' => trans('panel.favorites'),
                'favorites' => $favorites,
                'categories' => $categories,
                'instructors' => $instructors
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
