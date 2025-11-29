<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Translation\TestimonialTranslation;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;

class TestimonialsController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_testimonials_list');

            removeContentLocale();

            $testimonials = Testimonial::query()->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/comments.testimonials'),
                'testimonials' => $testimonials
            ];

            return view('admin.testimonials.lists', $data);
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
            $this->authorize('admin_testimonials_create');

            removeContentLocale();

            $data = [
                'pageTitle' => trans('admin/pages/comments.new_testimonial'),
            ];

            return view('admin.testimonials.create', $data);
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
            $this->authorize('admin_testimonials_create');

            $this->validate($request, [
                'user_avatar' => 'nullable|string',
                'user_name' => 'required|string',
                'user_bio' => 'required|string',
                'rate' => 'required|integer|between:0,5',
                'comment' => 'required|string',
            ]);

            $data = $request->all();

            if (empty($data['user_avatar'])) {
                $data['user_avatar'] = getPageBackgroundSettings('user_avatar');
            }

            $testimonial = Testimonial::create([
                'user_avatar' => $data['user_avatar'],
                'rate' => $data['rate'],
                'status' => $data['status'],
                'created_at' => time(),
            ]);

            if (!empty($testimonial)) {
                TestimonialTranslation::updateOrCreate([
                    'testimonial_id' => $testimonial->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'user_name' => $data['user_name'],
                    'user_bio' => $data['user_bio'],
                    'comment' => $data['comment'],
                ]);
            }

            return redirect(getAdminPanelUrl().'/testimonials');
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
            $this->authorize('admin_testimonials_edit');

            $testimonial = Testimonial::findOrFail($id);

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $testimonial->getTable(), $testimonial->id);

            $data = [
                'pageTitle' => trans('admin/pages/comments.edit_testimonial'),
                'testimonial' => $testimonial
            ];

            return view('admin.testimonials.create', $data);
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
            $this->authorize('admin_testimonials_edit');

            $this->validate($request, [
                'user_avatar' => 'nullable|string',
                'user_name' => 'required|string',
                'user_bio' => 'required|string',
                'rate' => 'required|integer|between:0,5',
                'comment' => 'required|string',
            ]);

            $testimonial = Testimonial::findOrFail($id);

            $data = $request->all();

            if (empty($data['user_avatar'])) {
                $data['user_avatar'] = getPageBackgroundSettings('user_avatar');
            }

            $testimonial->update([
                'user_avatar' => $data['user_avatar'],
                'rate' => $data['rate'],
                'status' => $data['status'],
            ]);

            TestimonialTranslation::updateOrCreate([
                'testimonial_id' => $testimonial->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'user_name' => $data['user_name'],
                'user_bio' => $data['user_bio'],
                'comment' => $data['comment'],
            ]);

            removeContentLocale();

            return redirect(getAdminPanelUrl().'/testimonials');
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
            $this->authorize('admin_testimonials_delete');

            $testimonial = Testimonial::findOrFail($id);

            $testimonial->delete();

            return redirect(getAdminPanelUrl().'/testimonials');
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function video()
    {
        try {
            removeContentLocale();

            $page="video";
            $this->authorize('admin_testimonials_video');

            $settings = Setting::where('page', 'other')
                ->get()
                ->keyBy('name');

            foreach ($settings as $setting) {
                $setting->value = json_decode($setting->value, true);
            }

            $data = [
                'pageTitle' => trans('admin/main.settings_title'),
                'settings' => $settings
            ];

            return view('admin.testimonials.' . $page, $data);
        } catch (\Exception $e) {
            \Log::error('video error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function editVideo($social_key)
    {
        try {
            removeContentLocale();

            $this->authorize('admin_testimonials_video_edit');
            $settings = Setting::where('name', 'video')->first();

            if (!empty($settings)) {
                $values = json_decode($settings->value);

                foreach ($values as $key => $value) {
                    if ($key == $social_key) {
                        $data = [
                            'pageTitle' => trans('admin/pages/setting.settings_socials'),
                            'social' => $value,
                            'socialKey' => $social_key,
                        ];

                        return view('admin.testimonials.video', $data);
                    }
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('editVideo error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function deleteVideo($social_key, $locale = null)
    {
        try {
            $this->authorize('admin_testimonials_video_delete');
            $settings = Setting::where('name', 'video')->first();

            if (empty($locale)) {
                $locale = Setting::$defaultSettingsLocale;
            }

            if (!empty($settings)) {
                $values = json_decode($settings->value);
                foreach ($values as $key => $value) {
                    if ($key == $social_key) {
                        unset($values->$social_key);
                    }
                }

                $settings = Setting::updateOrCreate(
                    ['name' => 'video'],
                    [
                        'page' => 'other',
                        'updated_at' => time(),
                    ]
                );

                SettingTranslation::updateOrCreate(
                    [
                        'setting_id' => $settings->id,
                        'locale' => mb_strtolower($locale)
                    ],
                    [
                        'value' => json_encode($values),
                    ]
                );

                cache()->forget('settings.' . 'video');

                return redirect(getAdminPanelUrl() . '/testimonials/video');
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('deleteVideo error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeVideo(Request $request)
    {
        try {
            $this->authorize('admin_testimonials_video_store');
            $this->validate($request, [
                'value.*' => 'required',
            ]);

            $data = $request->all();
            $locale = $request->get('locale', Setting::$defaultSettingsLocale);
            $social = $data['social'];
            $values = [];

            $settings = Setting::where('name', 'video')->first();

            if ($social !== 'newSocial') {
                if (!empty($settings) and !empty($settings->value)) {
                    $values = json_decode($settings->value);
                    foreach ($values as $key => $value) {
                        if ($key == $social) {
                            $values->$key = $data['value'];
                        }
                    }
                }
            } else {
                if (!empty($settings) and !empty($settings->value)) {
                    $values = json_decode($settings->value);
                }
                $key = str_replace(' ', '_', $data['value']['title']);
                $newValue[$key] = $data['value'];
                $values = array_merge((array)$values, $newValue);
            }

            $settings = Setting::updateOrCreate(
                ['name' => 'video'],
                [
                    'page' => 'other',
                    'updated_at' => time(),
                ]
            );

            SettingTranslation::updateOrCreate(
                [
                    'setting_id' => $settings->id,
                    'locale' => mb_strtolower($locale)
                ],
                [
                    'value' => json_encode($values),
                ]
            );

            cache()->forget('testimonials.' . 'video');

            return redirect(getAdminPanelUrl() . '/testimonials/video');
        } catch (\Exception $e) {
            \Log::error('storeVideo error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
