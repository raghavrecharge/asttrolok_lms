<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Subscribe;
use App\Models\Translation\SubscribeTranslation;
use Illuminate\Http\Request;

class SubscribesController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_subscribe_list');

            removeContentLocale();

            $subscribes = Subscribe::with([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/financial.subscribes'),
                'subscribes' => $subscribes
            ];

            return view('admin.financial.subscribes.lists', $data);
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
            $this->authorize('admin_subscribe_create');

            removeContentLocale();

            $data = [
                'pageTitle' => trans('admin/pages/financial.new_subscribe'),
            ];

            return view('admin.financial.subscribes.new', $data);
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
            $this->authorize('admin_subscribe_create');

            $this->validate($request, [
                'title' => 'required|string',
                'usable_count' => 'required|numeric',
                'days' => 'required|numeric',
                'price' => 'required|numeric',
                'icon' => 'required|string',
            ]);

            $data = $request->all();

            $subscribe = Subscribe::create([
                'usable_count' => $data['usable_count'],
                'days' => $data['days'],
                'price' => $data['price'],
                'icon' => $data['icon'],
                'is_popular' => (!empty($data['is_popular']) and $data['is_popular'] == '1'),
                'infinite_use' => (!empty($data['infinite_use']) and $data['infinite_use'] == '1'),
                'created_at' => time(),
            ]);

            if (!empty($subscribe)) {
                SubscribeTranslation::updateOrCreate([
                    'subscribe_id' => $subscribe->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => !empty($data['description']) ? $data['description'] : null,
                ]);
            }

            return redirect(getAdminPanelUrl().'/financial/subscribes');
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
            $this->authorize('admin_subscribe_edit');

            $subscribe = Subscribe::findOrFail($id);

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $subscribe->getTable(), $subscribe->id);

            $data = [
                'pageTitle' => trans('admin/pages/financial.new_subscribe'),
                'subscribe' => $subscribe
            ];

            return view('admin.financial.subscribes.new', $data);
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
            $this->authorize('admin_subscribe_edit');

            $this->validate($request, [
                'title' => 'required|string',
                'usable_count' => 'required|numeric',
                'days' => 'required|numeric',
                'price' => 'required|numeric',
                'icon' => 'required|string',
            ]);

            $data = $request->all();
            $subscribe = Subscribe::findOrFail($id);

            $subscribe->update([
                'usable_count' => $data['usable_count'],
                'days' => $data['days'],
                'price' => $data['price'],
                'icon' => $data['icon'],
                'is_popular' => (!empty($data['is_popular']) and $data['is_popular'] == '1'),
                'infinite_use' => (!empty($data['infinite_use']) and $data['infinite_use'] == '1'),
                'created_at' => time(),
            ]);

            SubscribeTranslation::updateOrCreate([
                'subscribe_id' => $subscribe->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => !empty($data['description']) ? $data['description'] : null,
            ]);

            removeContentLocale();

            return redirect(getAdminPanelUrl().'/financial/subscribes');
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
            $this->authorize('admin_subscribe_delete');

            $promotion = Subscribe::findOrFail($id);

            $promotion->delete();

            return redirect(getAdminPanelUrl().'/financial/subscribes');
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
