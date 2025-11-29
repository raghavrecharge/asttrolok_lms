<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use App\Models\WebinarReport;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function reasons(Request $request)
    {
        try {
            $this->authorize('admin_report_reasons');

            $value = [];

            $settings = Setting::where('name', 'report_reasons')->first();

            $locale = $request->get('locale', getDefaultLocale());
            storeContentLocale($locale, $settings->getTable(), $settings->id);

            if (!empty($settings) and !empty($settings->value)) {
                $value = json_decode($settings->value, true);
            }

            $data = [
                'pageTitle' => trans('admin/pages/setting.report_reasons'),
                'value' => $value,
            ];

            return view('admin.reports.reasons', $data);
        } catch (\Exception $e) {
            \Log::error('reasons error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeReasons(Request $request)
    {
        try {
            $this->authorize('admin_report_reasons');

            $name = 'report_reasons';

            $values = $request->get('value', null);

            if (!empty($values)) {
                $locale = $request->get('locale', getDefaultLocale());

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

            removeContentLocale();

            return back();
        } catch (\Exception $e) {
            \Log::error('storeReasons error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function webinarsReports()
    {
        try {
            $this->authorize('admin_webinar_reports');

            $reports = WebinarReport::with(['user' => function ($query) {
                $query->select('id', 'full_name');
            }, 'webinar' => function ($query) {
                $query->select('id', 'slug');
            }])->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/comments.classes_reports'),
                'reports' => $reports
            ];

            return view('admin.webinars.reports', $data);
        } catch (\Exception $e) {
            \Log::error('webinarsReports error: ' . $e->getMessage(), [
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
            $this->authorize('admin_webinar_reports_delete');

            $report = WebinarReport::findOrFail($id);

            $report->delete();

            return redirect()->back();
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
