<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class PagesController extends Controller
{
    public function index($link)
    {
        try {
            $firstCharacter = substr($link, 0, 1);
            if ($firstCharacter !== '/') {
                $link = '/' . $link;
            }

            $page = Page::where('link', $link)
                ->where('status', 'publish')
                ->first();

            if (!empty($page)) {
                $data = [
                    'pageTitle' => $page->title,
                    'pageDescription' => $page->seo_description,
                    'pageRobot' => $page->robot ? 'index, follow, all' : 'NOODP, nofollow, noindex',
                    'page' => $page
                ];

                $agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.pages.other_pages', $data);
                }else{
                    return view('web.default2' . '.pages.other_pages', $data);
                }

            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
