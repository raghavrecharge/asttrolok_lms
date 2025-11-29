<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\UserSession;
use App\Models\YtVedio;

use Illuminate\Support\Facades\Http;

class YtVideoController extends Controller
{

    public function index()
    {
        try {
            $YtVedio = YtVedio::get();

            return apiResponse2(1, 'retrieved',trans('api.public.retrieved'), $YtVedio);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

     public function latestVideos()
    {
        try {
            $apiKey = env('YOUTUBE_API_KEY');
            $channelId = "UCpTpt23TwNia1DV831JZgDg";

            $searchResponse = Http::get("https://www.googleapis.com/youtube/v3/search", [
                'key' => $apiKey,
                'channelId' => $channelId,
                'part' => 'snippet',
                'order' => 'date',
                'maxResults' => 5
            ]);

            if ($searchResponse->failed()) {
                return response()->json(['error' => 'Search API failed'], 500);
            }

            $videos = collect($searchResponse->json()['items'] ?? [])
                ->where('id.kind', 'youtube#video')
                ->values();

            if ($videos->isEmpty()) {
                return response()->json(['error' => 'No videos found'], 404);
            }

            $videoIds = $videos->pluck('id.videoId')->implode(',');

            $statsResponse = Http::get("https://www.googleapis.com/youtube/v3/videos", [
                'key' => $apiKey,
                'id' => $videoIds,
                'part' => 'statistics'
            ]);

            if ($statsResponse->failed()) {
                return response()->json(['error' => 'Stats API failed'], 500);
            }

            $statsMap = collect($statsResponse->json()['items'] ?? [])
                ->mapWithKeys(fn($item) => [$item['id'] => $item['statistics']]);

            $finalData = $videos->map(function ($video) use ($statsMap) {
                $videoId = $video['id']['videoId'];
                return [
                    'title' => $video['snippet']['title'],
                    'video_id' => $videoId,
                    'url' => "https://www.youtube.com/watch?v={$videoId}",
                    'published_at' => $video['snippet']['publishedAt'],
                    'thumbnail' => $video['snippet']['thumbnails']['high']['url'],
                    'views' => $statsMap[$videoId]['viewCount'] ?? 0,
                    'likes' => $statsMap[$videoId]['likeCount'] ?? 0,
                    'comments' => $statsMap[$videoId]['commentCount'] ?? 0
                ];
            });
            return apiResponse2(1, 'retrieved',trans('api.public.retrieved'), $finalData);
        } catch (\Exception $e) {
            \Log::error('latestVideos error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
