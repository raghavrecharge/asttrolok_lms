<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Certificate;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\Webinar;
use Illuminate\Http\Request;

class WebinarCertificateController extends Controller
{

    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            $webinars = Webinar::where('status', 'active')
                ->where('certificate', true)
                ->whereHas('sales', function ($query) use ($user) {
                    $query->where('buyer_id', $user->id);
                    $query->whereNull('refund_at');
                    $query->where('access_to_purchased_item', true);
                })
                ->get();

            $this->calculateCertificates($user, $webinars);

            $query = Certificate::whereNotNull('webinar_id')
                ->where('type', 'course')
                ->where('student_id', $user->id);

            $certificates = $this->handleFilters($query, $request)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('update.course_certificates'),
                'certificates' => $certificates,
                'userWebinars' => $webinars,
            ];

            return view('web.default.panel.certificates.webinar_certificates', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleFilters($query, $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $webinar_id = $request->get('webinar_id');

        fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($webinar_id)) {
            $query->where('webinar_id', $webinar_id);
        }

        return $query;
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
            $user = auth()->user();

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
