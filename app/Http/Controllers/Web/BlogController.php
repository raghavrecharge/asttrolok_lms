<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Webinar;
use App\User;
use App\Models\Role;
use App\Models\Translation\BlogTranslation;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
class BlogController extends Controller
{
    public function index(Request $request, $category = null)
    {
        
        $author = $request->get('author', null);
        $search = $request->get('search', null);
        $categories = $request->get('categories');

        $seoSettings = getSeoMetas('blog');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('home.blog');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('home.blog');
        $pageRobot = getPageRobot('blog');

        $blogCategories = BlogCategory::all();

        $query = Blog::where('status', 'publish')
            ->orderBy('created_at', 'desc');

        if (!empty($category)) {
            $blogCategory = $blogCategories->where('slug', $category)->first();
            if (!empty($blogCategory)) {
                $query->where('category_id', $blogCategory->id);
                $pageTitle .= ' ' . $blogCategory->title;
                $pageDescription .= ' ' . $blogCategory->title;
            }
        }
        if (!empty($categories)) {
                $query->whereIn('category_id', $categories);
        }

        if (!empty($author) and is_numeric($author)) {
            $query->where('author_id', $author);
        }

        if (!empty($search)) {
            $query->whereTranslationLike('title', "%$search%");
        }

        $blogCount = $query->count();

        $blog = $query->with([
            'category',
            'author' => function ($query) {
                $query->select('id', 'full_name', 'avatar', 'role_id', 'role_name');
            }
        ])
            ->withCount('comments')
            ->paginate(6);

        $popularPosts = $this->getPopularPosts();
        
        $popularWebinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
                ->get();
                
            if (!empty($category)) {
                $robots ='noindex';
                }else{
                   $robots ='index'; 
                }
        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'blog' => $blog,
            'blogCount' => $blogCount,
            'blogCategories' => $blogCategories,
            'popularWebinars' => $popularWebinars,
            'popularPosts' => $popularPosts,
            'page' =>'blog',
            'robots'=>$robots
        ];
 $agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.blog.index', $data);
            }else{
                return view('web.default2' . '.blog.index', $data);
            }
        // return view(getTemplate() . '.blog.index', $data);
    }

    public function show($slug)
    {
        if (!empty($slug)) {
            $post = Blog::where('slug', $slug)
                ->where('status', 'publish')
                ->with([
                    'category',
                    'author' => function ($query) {
                        $query->select('id', 'full_name', 'role_id', 'avatar', 'role_name');
                        $query->with('role');
                    },
                    'comments' => function ($query) {
                        $query->where('status', 'active');
                        $query->whereNull('reply_id');
                        $query->with([
                            'user' => function ($query) {
                                $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'role_id', 'role_name');
                            },
                            'replies' => function ($query) {
                                $query->where('status', 'active');
                                $query->with([
                                    'user' => function ($query) {
                                        $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'role_id', 'role_name');
                                    }
                                ]);
                            }
                        ]);
                    }])
                ->first();

            if (!empty($post)) {
                $post->update(['visit_count' => $post->visit_count + 1]);

                $blogCategories = BlogCategory::all();
                $popularPosts = $this->getPopularPosts();

                $pageRobot = getPageRobot('blog');

                $popularWebinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
                ->get();
                
                $consultant = User::where('role_name', Role::$teacher)
                ->select('id', 'full_name', 'avatar', 'bio')
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->where('ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('ban_end_at')
                                ->where('ban_end_at', '<', time());
                        });
                })
                ->inRandomOrder()->limit(8)
                ->get();

                $data = [
                    'pageTitle' => $post->title,
                    'pageDescription' => $post->meta_description,
                    'blogCategories' => $blogCategories,
                    'popularWebinars' => $popularWebinars,
                    'consultant' => $consultant  ?? [],
                    'popularPosts' => $popularPosts,
                    'pageRobot' => $pageRobot,
                    'post' => $post
                ];
$agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.blog.show', $data);
            }else{
                return view('web.default2' . '.blog.show', $data);
            }
                // return view(getTemplate() . '.blog.show', $data);
            }
            if (!empty($translate)) {
                app()->setLocale($translate->locale);


            }
        }

        abort(404);
    }

    private function getPopularPosts()
    {
        return Blog::where('status', 'publish')
            ->orderBy('visit_count', 'desc')
            ->limit(4)
            ->get();
    }
}
