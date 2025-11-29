<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\ForumFeaturedTopic;
use Illuminate\Http\Request;

class FeaturedTopicsController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_featured_topics_list');

            $featuredTopics = ForumFeaturedTopic::orderBy('created_at', 'desc')
                ->with([
                    'topic'
                ])
                ->paginate(10);

            $data = [
                'pageTitle' => trans('update.featured_topics'),
                'featuredTopics' => $featuredTopics
            ];

            return view('admin.forums.featured_topics.lists', $data);
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
            $this->authorize('admin_featured_topics_create');

            $data = [
                'pageTitle' => trans('update.new_featured_topic'),
            ];

            return view('admin.forums.featured_topics.create', $data);
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
            $this->authorize('admin_featured_topics_create');

            $this->validate($request, [
                'topic_id' => 'required|exists:forum_topics,id',
                'icon' => 'required'
            ]);

            $data = $request->all();

            ForumFeaturedTopic::create([
                'topic_id' => $data['topic_id'],
                'icon' => $data['icon'],
                'created_at' => time()
            ]);

            return redirect(getAdminPanelUrl().'/featured-topics');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit($id)
    {
        try {
            $this->authorize('admin_featured_topics_edit');

            $feature = ForumFeaturedTopic::where('id', $id)
                ->with([
                    'topic'
                ])
                ->first();

            if (!empty($feature)) {
                $data = [
                    'pageTitle' => trans('update.edit_featured_topic'),
                    'feature' => $feature
                ];

                return view('admin.forums.featured_topics.create', $data);
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
            $this->authorize('admin_featured_topics_edit');

            $this->validate($request, [
                'topic_id' => 'required|exists:forum_topics,id',
                'icon' => 'required'
            ]);

            $feature = ForumFeaturedTopic::findOrFail($id);

            $data = $request->all();

            $feature->update([
                'topic_id' => $data['topic_id'],
                'icon' => $data['icon'],
            ]);

            return redirect(getAdminPanelUrl().'/featured-topics');
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
            $this->authorize('admin_featured_topics_delete');

            $feature = ForumFeaturedTopic::findOrFail($id);

            $feature->delete();

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
