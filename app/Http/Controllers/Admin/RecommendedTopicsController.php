<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\ForumRecommendedTopic;
use App\Models\ForumRecommendedTopicItem;
use Illuminate\Http\Request;

class RecommendedTopicsController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_recommended_topics_list');

            $recommendedTopics = ForumRecommendedTopic::orderBy('created_at', 'desc')
                ->with([
                    'topics'
                ])
                ->paginate(10);

            $data = [
                'pageTitle' => trans('update.recommended_topics'),
                'recommendedTopics' => $recommendedTopics
            ];

            return view('admin.forums.recommended_topics.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function create()
    {
        try {
            $this->authorize('admin_recommended_topics_create');

            $data = [
                'pageTitle' => trans('update.new_recommended_topic'),
            ];

            return view('admin.forums.recommended_topics.create', $data);
        } catch (\Exception $e) {
            \Log::error('create error: ' . $e->getMessage(), [
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
            $this->authorize('admin_recommended_topics_create');

            $this->validate($request, [
                'topic_ids' => 'required|array|min:1',
                'title' => 'required|max:255',
                'icon' => 'required|max:255',
            ]);

            $data = $request->all();

            $recommended = ForumRecommendedTopic::create([
                'title' => $data['title'],
                'icon' => $data['icon'],
                'created_at' => time()
            ]);

            $this->handleTopicItems($recommended, $data['topic_ids']);

            return redirect(getAdminPanelUrl().'/recommended-topics');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleTopicItems($recommended, $topicIds)
    {
        ForumRecommendedTopicItem::where('recommended_topic_id', $recommended->id)
            ->delete();

        if (!empty($topicIds)) {
            foreach ($topicIds as $topicId) {
                ForumRecommendedTopicItem::create([
                    'recommended_topic_id' => $recommended->id,
                    'topic_id' => $topicId,
                    'created_at' => time(),
                ]);
            }
        }
    }

    public function edit($id)
    {
        try {
            $this->authorize('admin_recommended_topics_edit');

            $recommended = ForumRecommendedTopic::where('id', $id)
                ->with([
                    'topics'
                ])
                ->first();

            if (!empty($recommended)) {
                $data = [
                    'pageTitle' => trans('update.edit_recommended_topic'),
                    'recommended' => $recommended
                ];

                return view('admin.forums.recommended_topics.create', $data);
            }

            abort(404);
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
            $this->authorize('admin_recommended_topics_edit');

            $this->validate($request, [
                'topic_ids' => 'required|array|min:1',
                'title' => 'required|max:255',
                'icon' => 'required|max:255',
            ]);

            $recommended = ForumRecommendedTopic::findOrFail($id);

            $data = $request->all();

            $recommended->update([
                'title' => $data['title'],
                'icon' => $data['icon'],
            ]);

            $this->handleTopicItems($recommended, $data['topic_ids']);

            return redirect(getAdminPanelUrl().'/recommended-topics');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
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
            $this->authorize('admin_recommended_topics_delete');

            $recommended = ForumRecommendedTopic::findOrFail($id);

            $recommended->delete();

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
}
