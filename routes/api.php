<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NocApplicationController;
use App\Http\Controllers\common\AppCommonController;

Route::post('/application/view', [AppCommonController::class, 'viewApplication'])->middleware('keyclock_auth');

Route::post(
    '/noc/applications',
    [NocApplicationController::class, 'index']
);


Route::post('/land/details', [AppCommonController::class, 'landScheduleDetails']);
