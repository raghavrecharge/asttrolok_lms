<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        ValidationException::class,
        NotFoundHttpException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'captcha',
    ];

    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception)
    {
        // Log all errors except validation and 404s
        if (!$this->shouldntReport($exception)) {
            \Log::error('Exception Occurred', [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_id' => auth()->check() ? auth()->id() : 'Guest',
                'trace' => $exception->getTraceAsString()
            ]);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // -------------------------------------------------------
        // 🔥 API requests - Return JSON response
        // -------------------------------------------------------
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->renderApi($request, $exception);
        }

        // -------------------------------------------------------
        // 🔥 Validation errors - Keep normal behavior
        // -------------------------------------------------------
        if ($exception instanceof ValidationException) {
            return parent::render($request, $exception);
        }

        // -------------------------------------------------------
        // 🔥 HANDLE ALL ERRORS WITH POPUP (EVEN IN LOCAL)
        // Set SHOW_ERROR_POPUP=false in .env to disable
        // -------------------------------------------------------
        
        $showPopup = env('SHOW_ERROR_POPUP', true);
        
        if (!$showPopup) {
            // Show detailed Laravel error page
            return parent::render($request, $exception);
        }

        // Authentication errors - Redirect to login
        if ($exception instanceof AuthenticationException) {
            return $this->redirectWithPopup(
                '/login',
                'Please login to continue',
                'warning'
            );
        }

        // Authorization/Access denied - Redirect to home
        if ($exception instanceof AuthorizationException || 
            $exception instanceof AccessDeniedHttpException) {
            return $this->redirectWithPopup(
                '/',
                'You do not have permission to access this page',
                'error'
            );
        }

        // 404 Not Found - Redirect to home
        if ($exception instanceof NotFoundHttpException || 
            $exception instanceof ModelNotFoundException) {
            return $this->redirectWithPopup(
                '/',
                'The page you are looking for could not be found',
                'error'
            );
        }

        // Method Not Allowed - Redirect back
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->redirectWithPopup(
                url()->previous() ?: '/',
                'Invalid request method',
                'error'
            );
        }

        // Database errors - Redirect to home
        if ($this->isDatabaseError($exception)) {
            return $this->redirectWithPopup(
                '/',
                'A database error occurred. Please try again later',
                'error'
            );
        }

        // -------------------------------------------------------
        // 🔥 ALL OTHER ERRORS - Redirect to home with popup
        // -------------------------------------------------------
        return $this->redirectWithPopup(
            '/',
            'Something went wrong! Please try again later',
            'error'
        );
    }

    /**
     * Redirect with popup alert message
     */
    private function redirectWithPopup(string $url, string $message, string $type = 'error')
    {
        // Create popup data for session
        $popupData = [
            'show' => true,
            'message' => $message,
            'type' => $type, // success, error, warning, info
            'title' => $this->getPopupTitle($type)
        ];

        return redirect($url)
            ->with('popup', $popupData)
            ->with($type, $message); // Also flash as regular message for fallback
    }

    /**
     * Get popup title based on type
     */
    private function getPopupTitle(string $type): string
    {
        $titles = [
            'error' => 'Error!',
            'warning' => 'Warning!',
            'success' => 'Success!',
            'info' => 'Information'
        ];

        return $titles[$type] ?? 'Notice';
    }

    /**
     * Check if exception is database-related
     */
    private function isDatabaseError(Throwable $exception): bool
    {
        $databaseExceptions = [
            \Illuminate\Database\QueryException::class,
            \PDOException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        ];

        foreach ($databaseExceptions as $exceptionClass) {
            if ($exception instanceof $exceptionClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render API exceptions as JSON
     */
    private function renderApi($request, Throwable $exception)
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Internal Server Error';

        if ($exception instanceof MethodNotAllowedHttpException) {
            $status = Response::HTTP_METHOD_NOT_ALLOWED;
            $message = 'Method Not Allowed';

        } elseif ($exception instanceof NotFoundHttpException || 
                  $exception instanceof ModelNotFoundException) {
            $status = Response::HTTP_NOT_FOUND;
            $message = 'Resource Not Found';

        } elseif ($exception instanceof AuthorizationException || 
                  $exception instanceof AccessDeniedHttpException) {
            $status = Response::HTTP_FORBIDDEN;
            $message = 'Access Denied';

        } elseif ($exception instanceof AuthenticationException) {
            $status = Response::HTTP_UNAUTHORIZED;
            $message = 'Unauthenticated';

        } elseif ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validation Error',
                'errors' => $exception->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } elseif (config('app.debug')) {
            // In debug mode, show detailed error
            return parent::render($request, $exception);
        }

        return response()->json([
            'success' => false,
            'status' => $status,
            'message' => $message
        ], $status);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return $this->redirectWithPopup(
            '/login',
            'Please login to continue',
            'warning'
        );
    }
}