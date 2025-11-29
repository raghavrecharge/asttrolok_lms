<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\AgoraController;
use App\Http\Resources\SessionResource;
use App\Models\AgoraHistory;
use App\Models\Api\WebinarChapter;
use App\Models\File;
use App\Models\Sale;
use App\Models\Api\Session;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ZoomTokenService;
use Illuminate\Support\Carbon;
use Validator;
use Illuminate\Support\Facades\Http;

class SessionController extends Controller
{

     protected $zoomService;

    public function __construct(ZoomTokenService $zoomService)
    {
        $this->zoomService = $zoomService;
    }
    public function show($id)
    {
        try {
            $session = Session::where('id', $id)
                ->where('status', WebinarChapter::$chapterActive)->first();
            abort_unless($session, 404);
            if ($error = $session->canViewError()) {

            }
            $resource = new SessionResource($session);
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource);
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function BigBlueButton(Request $request)
    {
        try {
            $session_id = $request->input('session_id');
            $user = User::find($request->input('user_id'));
            Auth::login($user);

            return redirect(url('panel/sessions/' . $session_id . '/joinToBigBlueButton'));
        } catch (\Exception $e) {
            \Log::error('BigBlueButton error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function agora(Request $request)
    {
        try {
            $session_id = $request->input('session_id');
            $user = User::find($request->input('user_id'));
            Auth::login($user);

            return redirect(url('panel/sessions/' . $session_id . '/joinToAgora'));
        } catch (\Exception $e) {
            \Log::error('agora error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function getParticipants($meetingId)
{
        try {
            $response = $this->zoomService->getParticipants($meetingId);
            if($response){
            $participants = $response;

            foreach ($participants['participants'] as $participant) {
            echo 'Name: ' . $participant['name'] . ' - Email: ' . $participant['email'] . '<br>';
            }
            }else{
            return $response->body();
            }
        } catch (\Exception $e) {
            \Log::error('getParticipants error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
