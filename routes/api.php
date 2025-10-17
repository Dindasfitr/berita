<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BeritaController;

// Route::prefix('v1')->group(function () {
//     Route::apiResource('berita', BeritaController::class);
// });


Route::apiResource('berita', BeritaController::class);
