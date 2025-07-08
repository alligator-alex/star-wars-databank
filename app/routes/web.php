<?php

declare(strict_types=1);

use App\Modules\Databank\Public\Controllers\ExploreController;
use App\Modules\Databank\Public\Controllers\HomePageController;
use App\Modules\Databank\Public\Enums\DatabankRouteName;
use App\Modules\Droid\Public\Controllers\DroidController;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Vehicle\Public\Contollers\VehicleController;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomePageController::class, 'index'])
    ->name(DatabankRouteName::HOME);

Route::get('/explore/{type}/{slug}', [ExploreController::class, 'index'])
    ->name(DatabankRouteName::EXPLORE);

Route::prefix('/vehicles')->group(function () {
    Route::get('/', [VehicleController::class, 'index'])
        ->name(VehicleRouteName::INDEX);

    Route::get('/{slug}/', [VehicleController::class, 'detail'])
        ->name(VehicleRouteName::DETAIL);
});

Route::prefix('/droids')->group(function () {
    Route::get('/', [DroidController::class, 'index'])
        ->name(DroidRouteName::INDEX);

    Route::get('/{slug}/', [DroidController::class, 'detail'])
        ->name(DroidRouteName::DETAIL);
});
