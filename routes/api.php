<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NocApplicationController;
use App\Http\Controllers\common\AppCommonController;

Route::post('/application/view', [AppCommonController::class, 'viewApplication']);

Route::post(
    '/noc/applications',
    [NocApplicationController::class, 'index']
);