<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Prerequisite;
use App\Models\PrerequisiteWebinar;
use Illuminate\Http\Request;

class PrerequisiteController extends Controller
{
    public function store(Request $request)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $data = $request->all();

            $this->validate($request, [
                'webinar_id' => 'required',
                'prerequisite_id' => 'required|unique:prerequisites,prerequisite_id,null,id,webinar_id,' . $data['webinar_id'],
            ]);

            $required = (!empty($data['required']) and $data['required'] == 'on') ? true : false;

            Prerequisite::create([
                'webinar_id' => $data['webinar_id'],
                'prerequisite_id' => $data['prerequisite_id'],
                'required' => $required,
                'created_at' => time()
            ]);

            return response()->json([
                'code' => 200,
            ], 200);
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
            $this->authorize('admin_webinars_edit');

            if (!empty($request->get('item_id'))) {
                $Prerequisite = Prerequisite::select('required', 'webinar_id', 'prerequisite_id')
                    ->where('id', $id)
                    ->first();

                if (!empty($Prerequisite)) {
                    $Prerequisite->webinar_title = $Prerequisite->prerequisiteWebinar->title;

                    return response()->json([
                        'prerequisite' => $Prerequisite
                    ], 200);
                }
            }

            return response()->json([], 422);
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
            $this->authorize('admin_webinars_edit');

            $data = $request->all();

            $this->validate($request, [
                'webinar_id' => 'required',
                'prerequisite_id' => 'required|unique:prerequisites,prerequisite_id,' . $id . ',id,webinar_id,' . $data['webinar_id'],
            ]);

            $required = (!empty($data['required']) and $data['required'] == 'on') ? true : false;

            $prerequisite = Prerequisite::find($id);

            $prerequisite->update([
                'webinar_id' => $data['webinar_id'],
                'prerequisite_id' => $data['prerequisite_id'],
                'required' => $required,
                'updated_at' => time()
            ]);

            return response()->json([
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            Prerequisite::where('id',$id)->delete();

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
