<?php

namespace App\Http\Controllers\Api\Web;

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
        $YtVedio = YtVedio::get();

        return apiResponse2(1, 'retrieved',trans('api.public.retrieved'), $YtVedio);

    }
    
     public function latestVideos()
    {
        $apiKey = env('YOUTUBE_API_KEY');
        $channelId = "UCpTpt23TwNia1DV831JZgDg";

        // Step 1: Get latest 5 videos
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

        // Step 2: Collect video IDs
        $videoIds = $videos->pluck('id.videoId')->implode(',');

        // Step 3: Get statistics
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

        // Step 4: Merge snippet + stats
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
        // return response()->json($finalData);
    }
    
}
