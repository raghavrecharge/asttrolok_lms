<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeSectionSettingsController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_settings_personalization');

            removeContentLocale();

            $sections = HomeSection::orderBy('order', 'asc')->get();
            $selectedSectionsName = $sections->pluck('name')->toArray();

            $data = [
                'pageTitle' => trans('admin/main.home_sections'),
                'sections' => $sections,
                'selectedSectionsName' => $selectedSectionsName,
                'name' => 'home_sections'
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

            $this->validate($request, [
                'name' => 'required'
            ]);

            HomeSection::updateOrCreate([
                'name' => $request->get('name'),
            ], [
                'order' => HomeSection::query()->count() + 1
            ]);

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

    public function delete($id)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $section = HomeSection::findOrFail($id);

            $section->delete();

            $allSections = HomeSection::orderBy('order', 'asc')->get();

            $order = 1;
            foreach ($allSections as $allSection) {
                $allSection->update([
                    'order' => $order
                ]);

                $order += 1;
            }

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
                HomeSection::where('id', $id)
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
