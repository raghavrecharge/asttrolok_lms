<?php

namespace App\Http\Controllers\Api\Panel;

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
        $session = Session::where('id', $id)
            ->where('status', WebinarChapter::$chapterActive)->first();
        abort_unless($session, 404);
        if ($error = $session->canViewError()) {
            //       return $this->failure($error, 403, 403);
        }
        $resource = new SessionResource($session);
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource);
    }

    public function BigBlueButton(Request $request)
    {

        $session_id = $request->input('session_id');
        $user = User::find($request->input('user_id'));
        Auth::login($user);

        return redirect(url('panel/sessions/' . $session_id . '/joinToBigBlueButton'));

    }

    public function agora(Request $request)
    {

        $session_id = $request->input('session_id');
        $user = User::find($request->input('user_id'));
        Auth::login($user);

        return redirect(url('panel/sessions/' . $session_id . '/joinToAgora'));
    }
    public function getParticipants($meetingId)
{
    
     $response = $this->zoomService->getParticipants($meetingId);
    if($response){
    $participants = $response;
     // Process participants
    foreach ($participants['participants'] as $participant) {
        echo 'Name: ' . $participant['name'] . ' - Email: ' . $participant['email'] . '<br>';
    }
    }else{
        return $response->body();
    }

   
}

}
