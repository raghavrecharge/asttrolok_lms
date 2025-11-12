<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ForumTopic;
use App\Models\ForumTopicBookmark;
use App\Models\ForumTopicPost;
use Illuminate\Http\Request;
use App\Models\ForumTopicAttachment;
use App\Models\ForumTopicLike;
use App\User;
use App\Models\Reward;
use App\Models\RewardAccounting;
use Illuminate\Support\Facades\DB;
use App\Models\ForumFeaturedTopic;
use App\Models\ForumRecommendedTopic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
class ForumsController extends Controller
{
    
    
    public function index()
    {
        $forums = Forum::orderBy('order', 'asc')
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->with([
                'subForums' => function ($query) {
                    $query->where('status', 'active');
                    $query->withCount([
                        'topics',
                    ]);
                },
            ])
            ->withCount([
                'topics',
            ])
            ->get();

        foreach ($forums as $forum) {
            if (!empty($forum->subForums) and count($forum->subForums)) {
                foreach ($forum->subForums as $item) {
                    $item = $this->handleForumExtraData($item);
                }
            } else {
                $forum = $this->handleForumExtraData($forum);
            }
        }


        $seoSettings = getSeoMetas('forum');
        $pageTitle = $seoSettings['title'] ?? '';
        $pageDescription = $seoSettings['description'] ?? '';
        $pageRobot = getPageRobot('forum');

        $forumsCount = Forum::where('status', 'active')
            ->whereDoesntHave('subForums')
            ->count();

        $topicsCount = ForumTopic::query()->count();
        $postsCount = ForumTopicPost::query()->count();
        $membersCount = ForumTopicPost::select(DB::raw('count(distinct user_id) as count'))->first()->count;

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'forums' => $forums,
            'forumsCount' => $forumsCount,
            'topicsCount' => $topicsCount,
            'postsCount' => $postsCount,
            'membersCount' => $membersCount,
            'featuredTopics' => $this->getFeaturedTopics(),
            'recommendedTopics' => $this->getRecommendedTopics(),
        ];

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
    }
     private function handleForumExtraData(&$forum)
    {
        $topicsIds = ForumTopic::where('forum_id', $forum->id)->pluck('id')->toArray();

        $forum->posts_count = ForumTopicPost::whereIn('topic_id', $topicsIds)->count();

        $forum->lastTopic = ForumTopic::where('forum_id', $forum->id)->orderBy('created_at', 'desc')->first();

        return $forum;
    }
     public function storePost(Request $request)
    {
        $user = apiAuth();
         
         $forumSlug =  $request->get('forum_slug'); 
         $topicSlug =$request->get('topic_slug');

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->first();

            if (!empty($topic)) {
                $forum = $topic->forum;

                if (!$topic->close and !$forum->isClosed()) {
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

                    $post = ForumTopicPost::create([
                        'user_id' => $user->id,
                        'topic_id' => $topic->id,
                        'parent_id' => $replyPostId,
                        'description' => $data['description'],
                        'attach' => $data['attach'],
                        'created_at' => time(),
                    ]);

                    $buyStoreReward = RewardAccounting::calculateScore(Reward::SEND_TOPIC_POST);
                    RewardAccounting::makeRewardAccounting($post->user_id, $buyStoreReward, Reward::SEND_TOPIC_POST, $post->id);

                    $notifyOptions = [
                        '[topic_title]' => $topic->title,
                        '[u.name]' => $user->full_name
                    ];
                    sendNotification('send_post_in_topic', $notifyOptions, $topic->creator_id);

                    return response()->json([
                        'status'=>true,
                        'code' => 200,
                        'message'=> 'comment successfully store'
                    ]);
                }
            }
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    
    public function tutorialGuide()
    {
       


        $seoSettings = getSeoMetas('forum');
        $pageTitle = ' Step-by-Step Tutorial and Guide ';
        $pageDescription ='Get easy guides and video tutorials to help you use Asttrolok smoothly. Learn how to log in, access course content, reset your password, and set up meetings with instructors. Follow simple steps to buy courses, complete quizzes, and more. If you still have any questions, our support team is ready to help!';
        $pageRobot = getPageRobot('home');

        

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot
        ];
        $agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.forum.supports', $data);
                    }else{
                        return view('web.default2' . '.forum.supports', $data);
                    }

        return view('web.default.forum.supports', $data);
    }
    private function getFeaturedTopics()
    {
        $featuredTopics = ForumFeaturedTopic::orderBy('created_at', 'desc')
            ->with([
                'topic' => function ($query) {
                    $query->with([
                        'creator' => function ($query) {
                            $query->select('id', 'full_name', 'avatar');
                        },
                        'posts'
                    ]);
                    $query->withCount([
                        'posts'
                    ]);
                }
            ])->get();

        foreach ($featuredTopics as $featuredTopic) {
            $usersAvatars = [];

            if ($featuredTopic->topic->posts_count > 0) {
                foreach ($featuredTopic->topic->posts as $post) {
                    if (!empty($post->user) and count($usersAvatars) < 2 and empty($usersAvatars[$post->user->id])) {
                        $usersAvatars[$post->user->id] = $post->user;
                    }
                }
            }

            $featuredTopic->usersAvatars = $usersAvatars;
        }

        return $featuredTopics;
    }

    private function getRecommendedTopics()
    {
        return ForumRecommendedTopic::orderBy('created_at', 'desc')
            ->with([
                'topics'
            ])
            ->get();
    }
    
    public function topics(Request $request)
    {
        if (getFeaturesSettings('forums_status')) {
            $user = apiAuth();

            $forums = Forum::orderBy('order', 'asc')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->with([
                    'subForums' => function ($query) {
                        $query->where('status', 'active');
                    }
                ])->get();

            $query = ForumTopic::where('creator_id', $user->id);

            $publishedTopics = deepClone($query)->count();
            $lockedTopics = deepClone($query)->where('close', true)->count();

            $topicsIds = deepClone($query)->pluck('id')->toArray();
            $topicMessages = ForumTopicPost::whereIn('topic_id', $topicsIds)->count();

            $query = $this->handleFilters($request, $query);

            $topics = $query->orderBy('created_at', 'desc')
                ->with([
                    'forum'
                ])
                ->withCount([
                    'posts'
                ])
                ->paginate(10);

            $data = [
                'pageTitle' => trans('update.topics'),
                'forums' => $forums,
                'topics' => $topics,
                'publishedTopics' => $publishedTopics,
                'lockedTopics' => $lockedTopics,
                'topicMessages' => $topicMessages,
            ];
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }
    
    public function topicsdetailslist(Request $request,$slug)
    {
        
        //  $slug =  $request->get('slug'); 
         
        $forum = Forum::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!empty($forum)) {
            $query = ForumTopic::where('forum_topics.forum_id', $forum->id);

            $resultCount = 0;
            $topics = $this->handleTopics($request, $query, $resultCount);

            $data = [
                'pageTitle' => $forum->title,
                'pageDescription' => $forum->description,
                'pageRobot' => '',
                'forum' => $forum,
                'topics' => $topics,
                'topUsers' => $this->getTopUsers(),
                'popularTopics' => $this->getPopularTopics(),
                'resultCount' => $resultCount
            ];

             return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }
    
    public function topicsDetails(Request $request)
    {
        
         $user = apiAuth();
         
         $forumSlug =  $request->get('forum_slug'); 
         $topicSlug =$request->get('topic_slug');
    
        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($forum)) {
            $query = ForumTopic::where('forum_topics.forum_id', $forum->id);

            $resultCount = 0;
            $topics = $this->handleTopics($request, $query, $resultCount);

            $data = [
                'pageTitle' => $forum->title,
                'pageDescription' => $forum->description,
                'pageRobot' => '',
                'forum' => $forum,
                'topics' => $topics,
                'topUsers' => $this->getTopUsers(),
                'popularTopics' => $this->getPopularTopics(),
                'resultCount' => $resultCount
            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }
    
     private function handleTopics(Request $request, $query, &$resultCount)
    {
        $search = $request->get('search');
        $sort = $request->get('sort');

        if (!empty($search)) {
            $topicsIds = ForumTopicPost::where('description', 'like', "%$search%")
                ->pluck('topic_id')
                ->toArray();

            $query->where(function ($query) use ($topicsIds, $search) {
                $query->whereIn('forum_topics.id', $topicsIds)
                    ->orWhere('forum_topics.title', 'like', "%$search%")
                    ->orWhere('forum_topics.description', 'like', "%$search%");
            });
        }

        $query->orderBy('forum_topics.pin', 'desc');

        if (!empty($sort) and $sort != 'newest') {
            if ($sort == 'popular_topics') {
                $query->join('forum_topic_posts', 'forum_topic_posts.topic_id', 'forum_topics.id')
                    ->select('forum_topics.*', DB::raw("count(forum_topic_posts.topic_id) as topic_posts_count"))
                    ->orderBy('topic_posts_count', 'desc');
            } elseif ($sort == 'not_answered') {
                $query->whereDoesntHave('posts');
                $query->orderBy('forum_topics.created_at', 'desc');
            }
        } else {
            $query->orderBy('forum_topics.created_at', 'desc');
        }

        $resultCount = deepClone($query)->count();

        $topics = $query->with([
            'creator' => function ($query) {
                $query->select('id', 'full_name', 'avatar');
            }
        ])
            ->withCount([
                'posts'
            ])
            ->get();

        foreach ($topics as $topic) {
            $topic->lastPost = $topic->posts()->orderBy('created_at', 'desc')->first();
        }

        return $topics;
    }
    
    private function getTopUsers()
    {
        return User::leftJoin('forum_topics', 'forum_topics.creator_id', 'users.id')
            ->leftJoin('forum_topic_posts', 'forum_topic_posts.user_id', 'users.id')
            ->select('users.id', 'users.full_name', 'users.avatar', DB::raw("count(forum_topics.creator_id) as topics, count(forum_topic_posts.user_id) as posts"), DB::raw("(count(forum_topics.creator_id) + count(forum_topic_posts.user_id)) as all_posts"))
            ->whereHas('forumTopics')
            ->groupBy('forum_topics.creator_id')
            ->groupBy('forum_topic_posts.user_id')
            ->orderBy('all_posts', 'desc')
            ->limit(4)
            ->get();
    }

    private function getPopularTopics()
    {
        return ForumTopic::query()
            ->join('forum_topic_posts', 'forum_topic_posts.topic_id', 'forum_topics.id')
            ->select('forum_topics.*', DB::raw("count(forum_topic_posts.topic_id) as posts_count"))
            ->whereHas('creator')
            ->with([
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'avatar');
                }
            ])
            ->orderBy('posts_count', 'desc')
            ->groupBy('forum_topics.id')
            ->limit(4)
            ->get();
    }
     public function storeTopic(Request $request)
        {
          
          
     // Authenticate user
        $user = apiAuth();
    
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.'
            ], 401);
        }
    
            validateParam($request->all(),
            [
                'title' => 'required|max:255',
                'forum_id' => 'required|exists:forums,id',
                'description' => 'required',
            ]
        );
    
            $data = $request->all();
    
            $forum = Forum::where('id', $data['forum_id'])
                ->where('status', 'active')
                ->where('close', false)
                ->first();
            
    
            if (!empty($forum) and $forum->checkUserCanCreateTopic($user)) {
               $slug1= preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']);
                 $forum1 = ForumTopic::where('slug', $slug1)
                ->first();
               
               $forum_topic = ForumTopic::where('slug', $slug1)
                ->first();
               
                // if (!empty($forum1)) {
                // $slug1=$slug1.rand(2,50);
                // } 
                if (empty($forum_topic->slug)) {
                
                
    //             $pr=[
    //                 'slug' => ForumTopic::makeSlug($data['title']),
    //                 'creator_id' => $user->id,
    //                 'forum_id' => $data['forum_id'],
    //                 'title' => $data['title'],
    //                 'description' => $data['description'],
    //                 'close' => false,
    //                 'created_at' => time(),
    //             ];
    
    //  'slug' => ForumTopic::makeSlug($data['title']),
                $topic = ForumTopic::create([
                    'slug' => $slug1,
                    'creator_id' => $user->id,
                    'forum_id' => $data['forum_id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'close' => false,
                    'created_at' => time(),
                ]);
    
                $this->uploadProfileImage($topic, $request);
    
                $buyStoreReward = RewardAccounting::calculateScore(Reward::MAKE_TOPIC);
                RewardAccounting::makeRewardAccounting($topic->creator_id, $buyStoreReward, Reward::MAKE_TOPIC, $topic->id);
    
    
                $notifyOptions = [
                    '[u.name]' => $user->full_name,
                    '[topic_title]' => $topic->title,
                    '[forum_title]' => $forum->title,
                ];
                sendNotification("new_forum_topic", $notifyOptions, 1);
    
                $toastData = [
                    'title' => trans('public.request_success'),
                    'msg' => trans('update.new_topic_successfully_created'),
                    'status' => 'success'
                ];
    
                // $url = '/forums/' . $topic->forum->slug . '/topics';
                // return redirect($url)->with(['toast' => $toastData]);
                 return apiResponse2(1, 'stored', trans('update.new_topic_successfully_created'));
                }else{
                    
                    $error125="select another title This title has been chosen.";
                    // print_r($forum_topic);
                  return apiResponse2(0, 'error', $error125);
                    
                }
    
            }
            
        }
        
    private function handleTopicAttachments($topic, $data)
    {
         $user = apiAuth();
        ForumTopicAttachment::where('creator_id', $user->id)
            ->where('topic_id', $topic->id)
            ->delete();

        if (!empty($data['attachments']) and count($data['attachments'])) {

            foreach ($data['attachments'] as $attach) {
                if (!empty($attach)) {
                    ForumTopicAttachment::create([
                        'creator_id' => $user->id,
                        'topic_id' => $topic->id,
                        'path' => $attach,
                    ]);
                }
            }
        }
    }
    
    public function uploadProfileImage($topic, $request)
    {
        $user = apiAuth(); // Or use Auth::user()
        $urls = [];
    
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
    
            foreach ($files as $file) {
                // Upload to GCS
                $path = Storage::disk('gcs')->put('forum', $file);
                $url = Storage::disk('gcs')->url($path);
    
                // Save attachment to DB
                ForumTopicAttachment::create([
                    'creator_id' => $user->id,
                    'topic_id' => $topic->id,
                    'path' => $url,
                ]);
    
                $urls[] = $url;
            }
        }
    
        return $urls;
    }
    private function handleFilters(Request $request, $query, $type = null)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $forumId = $request->get('forum_id');
        $status = $request->get('status');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($forumId) and $forumId != 'all') {
            if ($type == 'posts') {
                $query->whereHas('topic', function ($query) use ($forumId) {
                    $query->where('forum_id', $forumId);
                });
            } else {
                $query->where('forum_id', $forumId);
            }
        }

        if ($status and $status !== 'all') {
            if ($type == 'posts') {
                $query->whereHas('topic', function ($query) use ($status) {
                    if ($status == 'closed') {
                        $query->where('close', true);
                    } else {
                        $query->where('close', false);
                    }
                });
            } else {
                if ($status == 'closed') {
                    $query->where('close', true);
                } else {
                    $query->where('close', false);
                }
            }
        }


        return $query;
    }

  public function posts(Request $request)
{
     
    
    try {
        $user = apiAuth();

        $forumSlug = $request->get('forum_slug'); 
        $topicSlug = $request->get('topic_slug');

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();
            

        if (!empty($forum) && $forum->checkUserCanCreateTopic()) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->with([
                    'forum',
                    'attachments',
                    'posts' => function ($query) {
                        $query->orderBy('pin', 'desc')
                              ->orderBy('created_at', 'asc')
                              ->with(['parent']);
                    }
                    
                ])
                ->first();
                
            if (!empty($topic)) {
                 $Userpost = User::select("id",'full_name', 'avatar')
                                            ->where('id', $topic->creator_id)
                                            ->first();
                
                $likedPostsIds = [];
                if (!empty($user)) {
                    $likedPostsIds = ForumTopicLike::where('user_id', $user->id)
                        ->pluck('topic_post_id')
                        ->toArray();

                    $topicLiked = ForumTopicLike::where('user_id', $user->id)
                        ->where('topic_id', $topic->id)
                        ->first();

                    $bookmarked = ForumTopicBookmark::where('user_id', $user->id)
                        ->where('topic_id', $topic->id)
                        ->first();

                    $topic->liked = !empty($topicLiked);
                    $topic->bookmarked = !empty($bookmarked);

                    if ($topic->posts) {
                        foreach ($topic->posts as $post) {
                            $postUser = User::select('full_name', 'avatar')
                                ->where('id', $post->user_id)
                                ->first();

                            $post->user = $postUser;
                        }
                    }
                }

                $data = [
                    'pageTitle' => $topic->title,
                    'pageDescription' => $topic->description,
                    'pageRobot' => '',
                    'forum' => $forum,
                    'topic' => $topic,
                    'postuser' => $Userpost,
                    'likedPostsIds' => $likedPostsIds,
                ];

                return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
            }

            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => "Topic not found.",
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => false,
            'status' => 'not_found',
            'message' => "Forum not found.",
            'data' => null
        ], 404);
    }

    // SQL-related errors
    catch (\Illuminate\Database\QueryException $e) {
        return response()->json([
            'success' => false,
            'status' => 'sql_error',
            'message' => 'A database error occurred.',
            'error' => $e->getMessage(), // Optional: hide in production
            'data' => null
        ], 500);
    }

    // General errors
    catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status' => 'server_error',
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(), // Optional: log instead of return
            'data' => null
        ], 500);
    }
}

    public function postsss(Request $request)
    {
       
        if (getFeaturesSettings('forums_status')) {
            $user = apiAuth();

            $forums = Forum::orderBy('order', 'asc')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->with([
                    'subForums' => function ($query) {
                        $query->where('status', 'active');
                    }
                ])->get();


            $query = ForumTopicPost::where('user_id', $user->id);

            $query = $this->handleFilters($request, $query, 'posts');

            $posts = $query->orderBy('created_at', 'desc')
                ->with([
                    'topic' => function ($query) {
                        $query->with([
                            'creator' => function ($query) {
                                $query->select('id', 'full_name', 'avatar');
                            },
                            'forum' => function ($query) {
                                $query->select('id', 'slug');
                            }
                        ]);
                    },
                    'user' => function ($query) {
                        $query->select('id', 'full_name', 'avatar', 'avatar_settings');
                    },
                ])
                ->get();

            foreach ($posts as $post) {
                $post->replies_count = ForumTopicPost::where('parent_id', $post->id)->count();
            }

            $data = [
                'pageTitle' => trans('site.posts'),
                'forums' => $forums,
                'posts' => $posts,
            ];
             return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function bookmarks()
    {
        if (getFeaturesSettings('forums_status')) {
            $user = apiAuth();

            $topicsIds = ForumTopicBookmark::where('user_id', $user->id)->pluck('topic_id')->toArray();

            $topics = ForumTopic::whereIn('id', $topicsIds)
                ->orderBy('created_at', 'desc')
                ->with([
                    'forum'
                ])
                ->withCount([
                    'posts'
                ])
                ->get();

        
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $topics);
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function removeBookmarks($topicId)
    {
        if (getFeaturesSettings('forums_status')) {
            $user = apiAuth();

            $bookmark = ForumTopicBookmark::where('user_id', $user->id)
                ->where('topic_id', $topicId)
                ->first();

            if (!empty($bookmark)) {
                $bookmark->delete();
            }

             return response()->json([
                    'status' => 'success',
                    'message' => 'Topic successfully deleted',
                ], 200);
        }

       return response()->json([
                    'status' => 'failed',
                    'message' => 'Topic id not exists',
                   
                ], 200);
    }
    
    public function topicLikeToggle(Request $request)
    {
         $user = apiAuth();
         
         $forumSlug =  $request->get('forum_slug'); 
         $topicSlug =$request->get('topic_slug');

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->first();

            if (!empty($topic)) {
                $like = ForumTopicLike::where('user_id', $user->id)
                    ->where('topic_id', $topic->id)
                    ->first();

                $likeStatus = true;
                if (!empty($like)) {
                    $like->delete();
                    $likeStatus = false;
                } else {
                    ForumTopicLike::create([
                        'user_id' => $user->id,
                        'topic_id' => $topic->id,
                    ]);
                }

                return response()->json([
                    'code' => 200,
                    'likes' => $topic->likes->count(),
                    'status' => $likeStatus
                ]);
            }
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }
    public function postLikeToggle($forumSlug, $topicSlug, $postId)
    {
        $user = apiAuth();

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {
                    $like = ForumTopicLike::where('user_id', $user->id)
                        ->where('topic_post_id', $postId)
                        ->first();

                    $likeStatus = true;
                    if (!empty($like)) {
                        $like->delete();
                        $likeStatus = false;
                    } else {
                        ForumTopicLike::create([
                            'user_id' => $user->id,
                            'topic_post_id' => $postId,
                        ]);
                    }

                    return response()->json([
                        'code' => 200,
                        'likes' => $post->likes->count(),
                        'status' => $likeStatus
                    ]);
                }
            }
        }


        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }


 public function topicBookmarkToggle(Request $request)
    {
        $user = apiAuth();
        $forumSlug =  $request->get('forum_slug'); 
        $topicSlug =$request->get('topic_slug');

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->first();

            if (!empty($topic)) {
                $add = true;
                $bookmark = ForumTopicBookmark::where('user_id', $user->id)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($bookmark)) {
                    $add = false;

                    $bookmark->delete();
                } else {
                    ForumTopicBookmark::create([
                        'user_id' => $user->id,
                        'topic_id' => $topic->id,
                        'created_at' => time(),
                    ]);
                }

                return response()->json([
                    'code' => 200,
                    'add' => $add
                ]);
            }
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function topicEdit(Request $request)
    {
         $user = apiAuth();
         
         $forumSlug =  $request->get('forum_slug'); 
         $topicSlug =$request->get('topic_slug');

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->where('creator_id', $user->id)
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
                    'pageTitle' => 'edit topic',
                    'pageDescription' => '',
                    'pageRobot' => '',
                    'forums' => $forums,
                    'topic' => $topic,
                ];

                 return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
            }
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function topicUpdate(Request $request, $forumSlug, $topicSlug)
    {
        $user = apiAuth();

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->where('creator_id', $user->id)
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

                $this->uploadProfileImage($topic, $request);

                $toastData = [
                    'title' => trans('public.request_success'),
                    'msg' => trans('update.new_topic_successfully_created'),
                    'status' => 'success'
                ];

                $url = '/forums/' . $topic->forum->slug . '/topics';
                return view('web.default.forum.create_topic', $data);
            }
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function postUnPin($forumSlug, $topicSlug, $postId)
    {
        $user = apiAuth();

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->where('creator_id', $user->id)
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
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function postPin($forumSlug, $topicSlug, $postId)
    {
        $user = apiAuth();

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->where('creator_id', $user->id)
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
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function postEdit($forumSlug, $topicSlug, $postId)
    {
        $user = apiAuth();

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('user_id', $user->id)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {

                    return response()->json([
                        'code' => 200,
                        'post' => $post
                    ]);
                }
            }
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function postUpdate(Request $request, $forumSlug, $topicSlug, $postId)
    {
        $user = apiAuth();

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->first();

            if (!empty($topic)) {

                $post = ForumTopicPost::where('id', $postId)
                    ->where('user_id', $user->id)
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
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }

    public function postDownloadAttachment($forumSlug, $topicSlug, $postId)
    {
        $user = apiAuth();

        $forum = Forum::where('slug', $forumSlug)
            ->where('status', 'active')
            ->first();

        if (!empty($user) and !empty($forum) and $forum->checkUserCanCreateTopic($user)) {
            $topic = ForumTopic::where('slug', $topicSlug)
                ->where('forum_id', $forum->id)
                ->first();

            if (!empty($topic)) {
                $post = ForumTopicPost::where('id', $postId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if (!empty($post)) {
                    $filePath = public_path($post->attach);

                    if (file_exists($filePath)) {
                        $fileInfo = pathinfo($filePath);
                        $type = (!empty($fileInfo) and !empty($fileInfo['extension'])) ? $fileInfo['extension'] : '';

                        $fileName = str_replace(' ', '-', "attachment-{$post->id}");
                        $fileName = str_replace('.', '-', $fileName);
                        $fileName .= '.' . $type;

                        $headers = array(
                            'Content-Type: application/' . $type,
                        );

                        return response()->download($filePath, $fileName, $headers);
                    }
                }
            }
        }

        return response()->json(['success' => false,'status' => 'not_found', 'message' => 'Something went wrong.','data' => null ],404);
    }
}
