<?php

declare(strict_types=1);

use App\Modules\Databank\Public\Controllers\HomePageController;
use App\Modules\Databank\Public\Controllers\VehicleController;
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class . '@index')
    ->name('home');

Route::prefix('/vehicles')->group(function () {
    Route::get('/', [VehicleController::class, 'list'])
        ->name(VehicleRouteName::LIST);

    Route::get('/{slug}/', [VehicleController::class, 'one'])
        ->name(VehicleRouteName::ONE);
});
