<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Kreait\Firebase\Factory;
use App\Http\Controllers\FCMController;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Lead Capture API Route
Route::post('/leads/submit', [\App\Http\Controllers\Api\LeadController::class, 'store']);

Route::prefix('/v1')->group(function () {

    //FCM Registration api
    Route::get('/registerfcm', [ApiController::class, 'registerFcm']);
    Route::get('/send-notification', [ApiController::class, 'sendNotification']);
    // REMOVED: /check-login — leaked credentials as plaintext JSON (Security Audit #23)

    Route::post('/enquiry', [ApiController::class, 'enquiryPost']);
    Route::get('/attendance', [ApiController::class, 'attendancePost']);

});