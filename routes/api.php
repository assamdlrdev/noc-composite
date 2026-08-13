<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NocApplicationController;
use App\Http\Controllers\common\AppCommonController;

Route::post('/noc/applications',[NocApplicationController::class, 'index']);

Route::get('/get-buyer-info',[AppCommonController::class, 'getBuyerDetails']);
Route::get('/get-seller-info',[AppCommonController::class, 'getSellerDetails']);

Route::post('/application/view', [AppCommonController::class, 'viewApplication'])->middleware('keyclock_auth');

Route::post(
    '/noc/applications',
    [NocApplicationController::class, 'index']
)->middleware('keyclock_auth');

Route::post(
    '/applications/workflow-status',
    [AppCommonController::class, 'status']
)->middleware('keyclock_auth');

Route::post('/land/details', [AppCommonController::class, 'landScheduleDetails']);
