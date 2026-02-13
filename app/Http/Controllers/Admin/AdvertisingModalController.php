<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvertisingModalController extends Controller
{
    protected $settingName = 'advertising_modal';

    public function index(Request $request)
    {
        try {
            $this->authorize('admin_advertising_modal_config');

            $value = [];

            $settings = Setting::where('name', $this->settingName)
                ->first();

            $defaultLocale = Setting::$defaultSettingsLocale;

            $locale = $request->get('locale', $defaultLocale);

            if (!empty($settings)) {
                storeContentLocale($locale, $settings->getTable(), $settings->id);

                if (!empty($settings->value)) {
                    $value = json_decode($settings->value, true);
                }
            }

            $data = [
                'pageTitle' => trans('update.advertising_modal'),
                'value' => $value,
                'selectedLocal' => $locale
            ];

            return view('admin.advertising_modal.index', $data);
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
            $values = $request->get('value', null);

            if (!empty($values)) {

                $defaultLocale = Setting::$defaultSettingsLocale;

                $locale = $request->get('locale', $defaultLocale);

                $values = array_filter($values, function ($val) {
                    if (is_array($val)) {
                        return array_filter($val);
                    } else {
                        return !empty($val);
                    }
                });

                $values = json_encode($values);
                $values = str_replace('record', rand(1, 600), $values);

                $setting = Setting::updateOrCreate(
                    ['name' => $this->settingName],
                    [
                        'page' => $request->get('page', 'other'),
                        'updated_at' => time(),
                    ]
                );

                SettingTranslation::updateOrCreate(
                    [
                        'setting_id' => $setting->id,
                        'locale' => mb_strtolower($locale)
                    ],
                    [
                        'value' => $values,
                    ]
                );

                cache()->forget('settings.' . $this->settingName);
            }

            removeContentLocale();

            return back();
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
