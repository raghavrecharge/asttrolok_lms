<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\HomePageStatistic;
use App\Models\HomeSection;
use App\Models\Setting;
use App\Models\Translation\HomePageStatisticTranslation;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatisticSettingsController extends Controller
{

    public function index()
    {
        try {
            $this->authorize('admin_settings_personalization');

            removeContentLocale();

            $name = 'statistics';
            $statistics = HomePageStatistic::orderBy('order', 'asc')->get();
            $settings = Setting::where('name', $name)->first();

            $values = null;

            if (!empty($settings)) {
                if (!empty($settings->value)) {
                    $values = json_decode($settings->value, true);
                }
            }

            $data = [
                'pageTitle' => trans('admin/main.statistics'),
                'statistics' => $statistics,
                'values' => $values,
                'name' => 'statistics'
            ];

            return view('admin.settings.personalization', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
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
            $this->authorize('admin_settings_personalization');
            $name = 'statistics';
            $page = 'personalization';

            $values = $request->get('value', null);

            if (!empty($values)) {
                $locale = Setting::$defaultSettingsLocale;

                $values = array_filter($values, function ($val) {
                    if (is_array($val)) {
                        return array_filter($val);
                    } else {
                        return !empty($val);
                    }
                });

                $values = json_encode($values);
                $values = str_replace('record', rand(1, 600), $values);

                $settings = Setting::updateOrCreate(
                    ['name' => $name],
                    [
                        'page' => $page,
                        'updated_at' => time(),
                    ]
                );

                SettingTranslation::updateOrCreate(
                    [
                        'setting_id' => $settings->id,
                        'locale' => mb_strtolower($locale)
                    ],
                    [
                        'value' => $values,
                    ]
                );

                cache()->forget('settings.' . $name);
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getForm()
    {
        try {
            $this->authorize('admin_settings_personalization');

            $data = [
                'locale' => mb_strtolower(app()->getLocale())
            ];

            $html = (string)view()->make('admin.settings.personalization.statistic_modal', $data);

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            \Log::error('getForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeItem(Request $request)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $data = $request->all();

            $validator = Validator::make($data, [
                "title" => "required",
                "description" => "required",
                "color" => "required",
                "icon" => "required",
                "count" => "required",
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $order = HomePageStatistic::query()->count() + 1;

            $item = HomePageStatistic::query()->create([
                "icon" => $data['icon'],
                "color" => $data['color'],
                "count" => $data['count'],
                "order" => $order,
                "created_at" => time(),
            ]);

            HomePageStatisticTranslation::query()->updateOrCreate([
                'home_page_statistic_id' => $item->id,
                'locale' => mb_strtolower($data['locale'])
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
            ]);

            return response()->json([
                'code' => 200
            ]);
        } catch (\Exception $e) {
            \Log::error('storeItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function editItem(Request $request, $id)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $statistic = HomePageStatistic::findOrFail($id);

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $statistic->getTable(), $statistic->id);

            $data = [
                'locale' => mb_strtolower($locale),
                'editStatistic' => $statistic
            ];

            $html = (string)view()->make('admin.settings.personalization.statistic_modal', $data);

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            \Log::error('editItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function updateItem(Request $request, $id)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $data = $request->all();

            $validator = Validator::make($data, [
                "title" => "required",
                "description" => "required",
                "color" => "required",
                "icon" => "required",
                "count" => "required",
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $statistic = HomePageStatistic::findOrFail($id);

            $statistic->update([
                "icon" => $data['icon'],
                "color" => $data['color'],
                "count" => $data['count'],
                "order" => $statistic->order,
            ]);

            HomePageStatisticTranslation::query()->updateOrCreate([
                'home_page_statistic_id' => $statistic->id,
                'locale' => mb_strtolower($data['locale'])
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
            ]);

            return response()->json([
                'code' => 200
            ]);
        } catch (\Exception $e) {
            \Log::error('updateItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function deleteItem($id)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $statistic = HomePageStatistic::findOrFail($id);

            $statistic->delete();

            $allSections = HomePageStatistic::orderBy('order', 'asc')->get();

            $order = 1;
            foreach ($allSections as $allSection) {
                $allSection->update([
                    'order' => $order
                ]);

                $order += 1;
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('deleteItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function sort(Request $request)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $data = $request->all();

            $validator = Validator::make($data, [
                'items' => 'required',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $itemIds = explode(',', $data['items']);

            foreach ($itemIds as $order => $id) {
                HomePageStatistic::where('id', $id)
                    ->update(['order' => ($order + 1)]);
            }

            return response()->json([
                'title' => trans('public.request_success'),
                'msg' => trans('update.items_sorted_successful')
            ]);
        } catch (\Exception $e) {
            \Log::error('sort error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
