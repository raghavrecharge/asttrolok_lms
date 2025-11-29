<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CertificateValidationController extends Controller
{
    public function index()
    {
        try {
            $getSeoMetas = getSeoMetas('certificate_validation');
            $pageTitle = !empty($getSeoMetas['title']) ? $getSeoMetas['title'] : trans('site.certificate_validation_page_title');
            $pageDescription = !empty($getSeoMetas['description']) ? $getSeoMetas['description'] : trans('site.certificate_validation_page_title');
            $pageRobot = getPageRobot('certificate_validation');

            $data = [
                'pageTitle' => $pageTitle,
                'pageDescription' => $pageDescription,
                'pageRobot' => $pageRobot,
            ];

            return view(getTemplate() . '.auth.certificate_validation', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function checkValidate(Request $request)
    {
        try {
            $data = $request->all();

            $validator = Validator::make($data, [
                'certificate_id' => 'required|numeric',
                'captcha' => 'required|captcha',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $certificateId = $data['certificate_id'];

            $certificate = Certificate::where('id', $certificateId)->first();

            if (!empty($certificate)) {
                $webinarTitle = "-";

                if ($certificate->type == 'quiz' and !empty($certificate->quiz) and !empty($certificate->quiz->webinar)) {
                    $webinarTitle = $certificate->quiz->webinar->title;
                } else if ($certificate->type == "course" and !empty($certificate->webinar)) {
                    $webinarTitle = $certificate->webinar->title;
                }

                $result = [
                    'student' => $certificate->student->full_name,
                    'webinar_title' => $webinarTitle,
                    'date' => dateTimeFormat($certificate->created_at, 'j F Y'),
                ];

                return response()->json([
                    'code' => 200,
                    'certificate' => $result
                ]);
            }

            return response()->json([
                'code' => 404,
            ]);
        } catch (\Exception $e) {
            \Log::error('checkValidate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
