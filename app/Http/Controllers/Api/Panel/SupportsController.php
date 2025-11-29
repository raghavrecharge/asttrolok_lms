<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Api\Objects\SupportObj;
use App\Models\Sale;
use App\Models\Api\Support;
use App\Models\SupportConversation;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\UploadFileManager ;
use Illuminate\Support\Facades\Storage;
class SupportsController extends Controller
{

    public function show(Request $request ,$id){
        try {
            $support = Support::where('id', $id)->first();
            if (!$support) {
                abort(404);
            }
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $support->details );
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function index(Request $request){
        try {
            $data = [
                'class_support' => $this->classSupport($request)->map(function ($support) {
                    return $support->details ;

                }),
                'my_class_support' => $this->myClassSupport($request)->map(function ($support) {
                    return $support->details ;
                }),
                'tickets' => $this->platformSupport($request)->map(function ($support) {
                    return $support->details ;
                }),
            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function myClassSupport(Request $request)
    {
        try {
            $user = apiAuth();
            $userWebinarsIds = $user->webinars->pluck('id')->toArray();

            $supports = Support::whereNull('department_id')
            ->WhereIn('webinar_id', $userWebinarsIds)
            ->handleFilters()->orderBy('created_at', 'desc')
                ->orderBy('status', 'asc')
                ->get()
                ->map(function ($support) {
                    return $support->details ;
                });

                return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $supports);
        } catch (\Exception $e) {
            \Log::error('myClassSupport error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function classSupport(Request $request){
        try {
            $user = apiAuth();
            $supports = Support::whereNull('department_id')
                ->where('user_id', $user->id)
                ->handleFilters()->orderBy('created_at', 'desc')
                ->orderBy('status', 'asc')
                ->get()->map(function ($support) {
                    return $support->details ;

                })  ;

                return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $supports);
        } catch (\Exception $e) {
            \Log::error('classSupport error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function platformSupport(Request $request)
    {
        try {
            $user = apiAuth();
            $supports = Support::whereNotNull('department_id')
                ->where('user_id', $user->id)->handleFilters()
                ->orderBy('created_at', 'desc')
                ->orderBy('status', 'asc')
                ->get()
                ->map(function ($support) {
                    return $support->details ;
                });
                return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $supports);
        } catch (\Exception $e) {
            \Log::error('platformSupport error: ' . $e->getMessage(), [
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
            $user = apiAuth();
            validateParam($request->all(), [
                'title' => 'required|min:2',
                'type' => 'required|in:course_support,platform_support',
                'department_id' => 'required_if:type,platform_support|nullable|exists:support_departments,id',
                'webinar_id' => 'required_if:type,course_support|nullable|exists:webinars,id',
                'message' => 'required|min:2',

            ]);

            $attach=null ;
            if($request->file('attach')){

               if ($request->hasFile('attach')) {
                $files = $request->file('attach');

                $path = Storage::disk('gcs')->put('suports', $files);
                $url = Storage::disk('gcs')->url($path);
                $attach = $url;
               }
            }

            $support = Support::create([
                'user_id' => $user->id,
                'department_id' => !empty($request->input('department_id')
                && $request->input('type')=='platform_support'
                ) ? $request->input('department_id') : null,
                'webinar_id' => !empty($request->input('webinar_id')
                && $request->input('type')=='course_support'
                ) ? $request->input('webinar_id') : null,
                'title' => $request->input('title'),
                'status' => 'open',
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            SupportConversation::create([
                'support_id' => $support->id,
                'sender_id' => $user->id,
                'message' => $request->input('message'),
                'attach' => $attach,
                'created_at' => time(),
            ]);

            if ($request->input('webinar_id')) {
                $webinar = Webinar::findOrFail($request->input('webinar_id'));

                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                    '[u.name]' => $user->full_name
                ];
                sendNotification('support_message', $notifyOptions, $webinar->teacher_id);
            }

            if ($request->input('department_id')) {
                $notifyOptions = [
                    '[s.t.title]' => $support->title,
                ];
                sendNotification('support_message_admin', $notifyOptions, 1);
            }

            return apiResponse2(1, 'stored', trans('api.public.stored'),[
                'attach'=>url($attach)
            ]);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeConversations(Request $request, $id)
    {
        try {
            validateParam($request->all(), [
                'message' => 'required|string|min:2',
            ]);

            $data = $request->all();

            $user = apiAuth();

            $userWebinarsIds = $user->webinars->pluck('id')->toArray();

            $support = Support::where('id', $id)
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                })->first();

            if (empty($support)) {
                return apiResponse2(0, 'failed', "data not found");
            }

            $support->update([
                'status' => ($support->user_id == $user->id) ? 'open' : 'replied',
                'updated_at' => time()
            ]);

            $attach=null ;
            if($request->file('attach')){

               if ($request->hasFile('attach')) {
                $files = $request->file('attach');

                $path = Storage::disk('gcs')->put('suports', $files);
                $url = Storage::disk('gcs')->url($path);
                $attach = $url;
               }
            }

            SupportConversation::create([
                'support_id' => $support->id,
                'sender_id' => $user->id,
                'message' => $request->input('message'),
                'attach' => $attach,
                'created_at' => time(),
            ]);

            if (!empty($support->webinar_id)) {
                $webinar = Webinar::findOrFail($support->webinar_id);

                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                ];
                sendNotification('support_message_replied', $notifyOptions, ($support->user_id == $user->id) ? $webinar->teacher_id : $user->id);
            }

            if (!empty($support->department_id)) {
                $notifyOptions = [
                    '[s.t.title]' => $support->title,
                ];
                sendNotification('support_message_replied_admin', $notifyOptions, 1);
            }

            return apiResponse2(1, 'stored', trans('api.public.stored'),[
                'attach'=>url($attach)
            ]);
        } catch (\Exception $e) {
            \Log::error('storeConversations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function close($id)
    {
        try {
            $user = apiAuth();
            $userWebinarsIds = $user->webinars->pluck('id')->toArray();

            $support = Support::where('id', $id)
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                })->first();

            if (empty($support)) {
                return apiResponse2(0, 'failed', "data not found");
            }

            $support->update([
                'status' => 'close',
                'updated_at' => time()
            ]);

            return apiResponse2(1, 'closed', trans('api.support.closed') );
        } catch (\Exception $e) {
            \Log::error('close error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
