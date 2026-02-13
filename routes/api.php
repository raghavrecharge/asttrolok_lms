<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\UserController;
use Symfony\Component\HttpKernel\HttpCache\Store;
use App\Http\Controllers\Api\Web\FilesController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

  Route::post('development/chapters', [FilesController::class, 'createChapter']);

  
    Route::post('development/chapters/{chapter}/files', [FilesController::class, 'storeFile']);

  
    Route::post('development/chapters-with-file', [FilesController::class, 'storeChapterAndFile']);

    // optional: create file without path param (send chapter_id in body)
    Route::post('development/files', [FilesController::class, 'storeFile']);
// Route::post('/development/files', [FilesController::class, 'store']);
Route::get('/development/files', [FilesController::class, 'store']);
Route::get('/development/chapters', [FilesController::class, 'chapterstore']);




Route::group(['prefix' => '/development'], function () {
   
    Route::post('/login', 'LoginController@requestOtp');

    Route::get('/', function () {
        return 'api test';
    });
    
    Route::get('/profile/{id}', ['uses' => 'UserController@profile']);

    Route::middleware('api') ->group(base_path('routes/api/auth.php'));

    Route::namespace('Web')->group(base_path('routes/api/guest.php'));

    Route::prefix('panel')->middleware('api.auth')->namespace('Panel')->group(base_path('routes/api/user.php'));

    Route::group(['namespace' => 'Config', 'middleware' => []], function () {
        Route::get('/config', ['uses' => 'ConfigController@list']);
    });

    Route::prefix('instructor')->middleware(['api.auth', 'api.level-access:teacher'])->namespace('Instructor')->group(base_path('routes/api/instructor.php'));

    // Unified Payment Engine
    Route::prefix('upe')->group(base_path('routes/api/upe.php'));

});
