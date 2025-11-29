<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarCertificateResource;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Certificate;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Webinar;
use Illuminate\Http\Request;

class WebinarCertificateController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = apiAuth();

            $webinars = Webinar::where('status', 'active')
                ->whereHas('sales', function ($query) use ($user) {
                    $query->where('buyer_id', $user->id);
                    $query->whereNull('refund_at');
                    $query->where('access_to_purchased_item', true);
                })
                ->get();

            $this->calculateCertificates($user, $webinars);

            $certificates = Certificate::whereNotNull('webinar_id')
                ->where('type', 'course')
                ->where('student_id', $user->id)->orderBy('created_at', 'desc')
                ->get();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'certificates' => WebinarCertificateResource::collection($certificates),

                ]);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function show($id)
    {
        try {
            return $this->makeCertificate($id);
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function calculateCertificates($user, $webinars)
    {
        $makeCertificate = new MakeCertificate();

        foreach ($webinars as $webinar) {
            if ($webinar->certificate and $webinar->getProgress() >= 100) {
                $check = Certificate::where('type', 'course')
                    ->where('student_id', $user->id)
                    ->where('webinar_id', $webinar->id)
                    ->first();

                if (empty($check)) {
                    $userCertificate = $makeCertificate->saveCourseCertificate($user, $webinar);

                    $certificateReward = RewardAccounting::calculateScore(Reward::CERTIFICATE);
                    RewardAccounting::makeRewardAccounting($userCertificate->student_id, $certificateReward, Reward::CERTIFICATE, $userCertificate->id, true);
                }
            }
        }
    }

    public function makeCertificate($certificateId)
    {
        try {
            $user = apiAuth();

            $certificate = Certificate::where('id', $certificateId)
                ->where('student_id', $user->id)
                ->whereNotNull('webinar_id')
                ->first();

            if (!empty($certificate)) {
                $makeCertificate = new MakeCertificate();

                return $makeCertificate->makeCourseCertificate($certificate);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('makeCertificate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
