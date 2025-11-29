<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Webinar;

class FavoriteController extends Controller
{
    public function toggle($slug)
    {
        try {
            $userId = auth()->id();
            $webinar = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($webinar)) {

                $isFavorite = Favorite::where('webinar_id', $webinar->id)
                    ->where('user_id', $userId)
                    ->first();

                if (empty($isFavorite)) {
                    Favorite::create([
                        'user_id' => $userId,
                        'webinar_id' => $webinar->id,
                        'created_at' => time()
                    ]);
                } else {
                    $isFavorite->delete();
                }
            }

            return response()->json([], 200);
        } catch (\Exception $e) {
            \Log::error('toggle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
