<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NocApplicationController;

Route::post(
    '/noc/applications',
    [NocApplicationController::class, 'index']
);