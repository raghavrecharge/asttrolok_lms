<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingBanner;
use App\Models\Translation\AdvertisingBannerTranslation;
use Illuminate\Http\Request;

class AdvertisingBannersController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_advertising_banners');

            $banners = AdvertisingBanner::paginate(15);

            $data = [
                'pageTitle' => trans('admin/main.advertising_banners_list'),
                'banners' => $banners
            ];

            return view('admin.advertising.banner.lists', $data);
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
            $this->authorize('admin_advertising_banners_create');

            $data = [
                'pageTitle' => trans('admin/main.new_banner')
            ];

            return view('admin.advertising.banner.create', $data);
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
            $this->authorize('admin_advertising_banners_create');

            $this->validate($request, [
                'title' => 'required',
                'position' => 'required',
                'image' => 'required',
                'size' => 'required',
                'link' => 'required',
            ]);

            $data = $request->all();

            $banner = AdvertisingBanner::create([
                'position' => $data['position'],
                'size' => $data['size'],
                'link' => $data['link'],
                'published' => $data['published'],
                'created_at' => time(),
            ]);

            AdvertisingBannerTranslation::updateOrCreate([
                'advertising_banner_id' => $banner->id,
                'locale' => mb_strtolower($data['locale'])
            ], [
                'title' => $data['title'],
                'image' => $data['image'],
            ]);

            return redirect(getAdminPanelUrl().'/advertising/banners');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('admin_advertising_banners_edit');

            $banner = AdvertisingBanner::findOrFail($id);

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $banner->getTable(), $banner->id);

            $data = [
                'pageTitle' => trans('admin/main.edit'),
                'banner' => $banner
            ];

            return view('admin.advertising.banner.create', $data);
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
            $this->authorize('admin_advertising_banners_edit');

            $this->validate($request, [
                'title' => 'required',
                'position' => 'required',
                'image' => 'required',
                'size' => 'required',
                'link' => 'required',
            ]);

            $data = $request->all();

            $banner = AdvertisingBanner::findOrFail($id);

            $banner->update([
                'position' => $data['position'],
                'size' => $data['size'],
                'link' => $data['link'],
                'published' => $data['published'],
            ]);

            AdvertisingBannerTranslation::updateOrCreate([
                'advertising_banner_id' => $banner->id,
                'locale' => mb_strtolower($data['locale'])
            ], [
                'title' => $data['title'],
                'image' => $data['image'],
            ]);

            removeContentLocale();

            return redirect(getAdminPanelUrl().'/advertising/banners');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->authorize('admin_advertising_banners_delete');

            $banner = AdvertisingBanner::findOrFail($id);

            $banner->delete();

            return redirect(getAdminPanelUrl().'/advertising/banners');
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
