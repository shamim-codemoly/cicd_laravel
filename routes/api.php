<?php

use App\Http\Controllers\GtmConfigController;
use Illuminate\Support\Facades\Route;


    Route::post('/gtm-server-container-config', [GtmConfigController::class, 'gtmServerContainerConfig']);
