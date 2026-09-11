<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SunatDispatchController;
use App\Http\Controllers\Api\SunatBillingPayloadController;
use App\Http\Controllers\Api\SunatValidationController;

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

Route::post('/sunat/validate', SunatValidationController::class);
Route::get('/sunat/billings/{billing}/payload', SunatBillingPayloadController::class);
Route::post('/sunat/billings/{billing}/dispatch', SunatDispatchController::class);
