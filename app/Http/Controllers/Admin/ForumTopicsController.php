<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ForumTopic;
use App\Models\ForumTopicAttachment;
use App\Models\ForumTopicPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumTopicsController extends Controller
{

     public function index(Request $request, $forumId)
    {
        try {
            $this->authorize('admin_forum_topics_lists');

            if ($forumId ==0) {
                $query = ForumTopic::whereIn('status', [1,0]);
            }else {
                $forum = Forum::findOrFail($forumId);
                $query = ForumTopic::where('forum_id', $forum->id);
            }

            $query = $this->handleFilers($request, $query);

            $topics = $query
                ->orderBy('created_at', 'desc')
                ->with([
                    'creator' => function ($query) {
                        $query->select('id', 'full_name', 'avatar');
                    },
                    'posts' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    }
                ])
                ->withCount([
                    'posts'
                ])
                ->paginate(10);

            foreach ($topics as $topic) {
                $topic->lastPost = $topic->posts()->orderBy('created_at', 'desc')->first();
            }

            $data = [
                'pageTitle' => trans('update.topics'),
                'topics' => $topics
            ];

            return view('admin.forums.topics.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleFilers(Request $request, $query)
    {
        $search = $request->get('search');

        if (!empty($search)) {
            $topicsIds = ForumTopicPost::where('description', 'like', "%$search%")
                ->pluck('topic_id')
                ->toArray();

            $query->where(function ($query) use ($topicsIds, $search) {
                $query->whereIn('id', $topicsIds)
                    ->orWhere('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        return $query;
    }

    public function create()
    {
        try {
            $this->authorize('admin_forum_topics_create');

            $forums = Forum::orderBy('order', 'asc')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->with([
                    'subForums' => function ($query) {
                        $query->where('status', 'active');
                    }
                ])->get();

            $data = [
                'pageTitle' => trans('update.create_new_topic'),
                'pageDescription' => '',
                'pageRobot' => '',
                'forums' => $forums
            ];

            return view('admin.forums.topics.create', $data);
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
            $this->authorize('admin_forum_topics_create');

            $user = auth()->user();

            $this->validate($request, [
                'title' => 'required|max:255',
                'forum_id' => 'required|exists:forums,id',
                'description' => 'required',
            ]);

            $data = $request->all();

            $forum = Forum::where('id', $data['forum_id'])
                ->where('status', 'active')
                ->where('close', false)
                ->first();

            if (!empty($forum)) {

                $topic = ForumTopic::create([
                    'slug' => ForumTopic::makeSlug($data['title']),
                    'creator_id' => $user->id,
                    'forum_id' => $data['forum_id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'close' => false,
                    'created_at' => time(),
                ]);

                $this->handleTopicAttachments($topic, $data);

                $toastData = [
                    'title' => trans('public.request_success'),
                    'msg' => trans('update.new_topic_successfully_created'),
                    'status' => 'success'
                ];

                $url = getAdminPanelUrl('/forums/' . $topic->forum_id . '/topics');
                return redirect($url)->with(['toast' => $toastData]);

            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit($forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_create');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $forums = Forum::orderBy('order', 'asc')
                    ->whereNull('parent_id')
                    ->where('status', 'active')
                    ->with([
                        'subForums' => function ($query) {
                            $query->where('status', 'active');
                        }
                    ])->get();

                $data = [
                    'pageTitle' => trans('admin/main.edit'),
                    'pageDescription' => '',
                    'pageRobot' => '',
                    'forums' => $forums,
                    'topic' => $topic,
                ];

                return view('admin.forums.topics.create', $data);
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_create');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $this->validate($request, [
                    'title' => 'required|max:255',
                    'forum_id' => 'required|exists:forums,id',
                    'description' => 'required',
                ]);

                $data = $request->all();

                $topic->update([
                    'forum_id' => $data['forum_id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'close' => false,
                ]);

                $this->handleTopicAttachments($topic, $data);

                $toastData = [
                    'title' => trans('public.request_success'),
                    'msg' => trans('update.new_topic_successfully_created'),
                    'status' => 'success'
                ];

                $url = getAdminPanelUrl('/forums/' . $topic->forum_id . '/topics');
                return redirect($url)->with(['toast' => $toastData]);
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleTopicAttachments($topic, $data)
    {
        $user = auth()->user();

        ForumTopicAttachment::where('topic_id', $topic->id)
            ->delete();

        if (!empty($data['attachments']) and count($data['attachments'])) {

            foreach ($data['attachments'] as $attach) {
                if (!empty($attach)) {
                    ForumTopicAttachment::create([
                        'creator_id' => $topic->creator_id,
                        'topic_id' => $topic->id,
                        'path' => $attach,
                    ]);
                }
            }
        }
    }

    public function delete(Request $request,$forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_delete');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $topic->delete();

                $toastData = [
                    'title' => trans('public.request_success'),
                    'msg' => trans('update.topic_successfully_delete'),
                    'status' => 'success'
                ];

                if (!empty($request->get('no_redirect'))) {
                    return back()->with(['toast' => $toastData]);
                }

                $url = getAdminPanelUrl('/forums/' . $topic->forum_id . '/topics');
                return redirect($url)->with(['toast' => $toastData]);
            }
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function closeToggle(Request $request, $forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_lists');

            $topic = ForumTopic::findOrFail($topicId);

            $data = $request->all();

            $topic->update([
                'close' => ((!empty($data['close']) and $data['close'] == 1))
            ]);

            return back();
        } catch (\Exception $e) {
            \Log::error('closeToggle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function close($forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_lists');

            $topic = ForumTopic::findOrFail($topicId);

            $topic->update([
                'close' => true
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.topic_closed_successful'),
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('close error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function open($forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_lists');

            $topic = ForumTopic::findOrFail($topicId);

            $topic->update([
                'close' => false
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.topic_opened_successful'),
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('open error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function posts($forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_posts');

            $forum = Forum::findOrFail($forumId);

            $topic = ForumTopic::where('forum_id', $forumId)
                ->where('id', $topicId)
                ->with([
                    'posts' => function ($query) {
                        $query->orderBy('pin', 'desc');
                        $query->orderBy('created_at', 'asc');
                        $query->with([
                            'parent',
                            'user'
                        ]);
                    }
                ])
                ->first();

            if (!empty($topic)) {
                $data = [
                    'pageTitle' => trans('site.posts'),
                    'topic' => $topic,
                    'forum' => $forum
                ];

                return view('admin.forums.topics.posts', $data);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('posts error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storePost(Request $request, $forumId, $topicId)
    {
        try {
            $this->authorize('admin_forum_topics_create_posts');

            $user = auth()->user();

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $data = $request->all();

                $validator = Validator::make($data, [
                    'description' => 'required|min:3'
                ]);

                if ($validator->fails()) {
                    return response([
                        'code' => 422,
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $replyPostId = (!empty($data['reply_post_id']) and $data['reply_post_id'] != '') ? $data['reply_post_id'] : null;

                ForumTopicPost::create([
                    'user_id' => $user->id,
                    'topic_id' => $topic->id,
                    'parent_id' => $replyPostId,
                    'description' => $data['description'],
                    'attach' => $data['attach'],
                    'created_at' => time(),
                ]);

                return response()->json([
                    'code' => 200
                ]);
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('storePost error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function postEdit($forumId, $topicId, $postId)
    {
        try {
            $this->authorize('admin_forum_topics_create_posts');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {

                    return response()->json([
                        'code' => 200,
                        'post' => $post
                    ]);
                }
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('postEdit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function postUpdate(Request $request, $forumId, $topicId, $postId)
    {
        try {
            $this->authorize('admin_forum_topics_create_posts');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {

                    $data = $request->all();

                    $validator = Validator::make($data, [
                        'description' => 'required|min:3'
                    ]);

                    if ($validator->fails()) {
                        return response([
                            'code' => 422,
                            'errors' => $validator->errors(),
                        ], 422);
                    }

                    $post->update([
                        'description' => $data['description'],
                        'attach' => $data['attach'],
                    ]);

                    return response()->json([
                        'code' => 200
                    ]);
                }
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('postUpdate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function postUnPin($forumId, $topicId, $postId)
    {
        try {
            $this->authorize('admin_forum_topics_create_posts');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {
                    $post->update([
                        'pin' => false
                    ]);

                    return response()->json([
                        'code' => 200,
                    ]);
                }
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('postUnPin error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function postPin($forumId, $topicId, $postId)
    {
        try {
            $this->authorize('admin_forum_topics_create_posts');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {
                    $post->update([
                        'pin' => true
                    ]);

                    return response()->json([
                        'code' => 200,
                    ]);
                }
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('postPin error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function postDelete($forumId, $topicId, $postId)
    {
        try {
            $this->authorize('admin_forum_topics_create_posts');

            $topic = ForumTopic::where('id', $topicId)
                ->where('forum_id', $forumId)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {
                    $post->delete();

                    $toastData = [
                        'title' => trans('public.request_success'),
                        'msg' => trans('update.post_successfully_delete'),
                        'status' => 'success'
                    ];

                    return redirect()->back()->with(['toast' => $toastData]);
                }
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('postDelete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function status(Request $request)
    {
        try {
            $this->authorize('admin_forum_topics_create_posts');

              $topicId= $request->topic_id;
              $status= $request->status;

                if (!empty($topicId)) {
                    $post = ForumTopic::where('id', $topicId)
                        ->first();

                    if (!empty($post)) {
                        $post->update([
                            'status' => $status
                        ]);

                        return response()->json([
                            'code' => 200,
                        ]);
                    }
                }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('status error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
