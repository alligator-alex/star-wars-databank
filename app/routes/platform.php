<?php

declare(strict_types=1);

use App\Modules\Core\Admin\Controllers\User\ProfileController;
use App\Modules\Core\Admin\Enums\AdminRouteName;
use App\Modules\Core\Admin\Enums\UserRouteName;
use App\Modules\Databank\Admin\Controllers\HomePageController;
use App\Modules\Databank\Admin\Controllers\SettingsPageController;
use App\Modules\Droid\Admin\Controllers\DroidDetailController;
use App\Modules\Droid\Admin\Controllers\DroidIndexController;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Faction\Admin\Controllers\FactionDetailController;
use App\Modules\Faction\Admin\Controllers\FactionIndexController;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Handbook\Admin\Controllers\HandbookValueDetailController;
use App\Modules\Handbook\Admin\Controllers\HandbookValueIndexController;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Manufacturer\Admin\Controllers\ManufacturerDetailController;
use App\Modules\Manufacturer\Admin\Controllers\ManufacturerIndexController;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Media\Admin\Controllers\MediaDetailController;
use App\Modules\Media\Admin\Controllers\MediaIndexController;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Vehicle\Admin\Controllers\VehicleDetailController;
use App\Modules\Vehicle\Admin\Controllers\VehicleIndexController;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Home
Route::prefix('/dashboard')->group(static function () {
    Route::get('/', HomePageController::class)
        ->name(AdminRouteName::HOME);
});

// Settings
Route::prefix('/settings')->group(static function () {
    Route::get('/', SettingsPageController::class)
        ->name(AdminRouteName::SETTINGS);

    Route::post('/clear-cache', [SettingsPageController::class, 'clearCache'])
        ->name(AdminRouteName::CLEAR_CACHE);
});

// Profile
Route::prefix('/profile')->group(static function () {
    Route::get('/', ProfileController::class)
        ->name(UserRouteName::PROFILE);

    Route::post('/', [ProfileController::class, 'update'])
        ->name(UserRouteName::UPDATE);

    Route::post('/change-password', [ProfileController::class, 'changePassword'])
        ->name(UserRouteName::CHANGE_PASSWORD);
});

// Handbooks
Route::prefix('/handbook')->group(static function () {
    Route::get('/{handbookId}/values', HandbookValueIndexController::class)
        ->name(HandbookValueRouteName::INDEX);
    Route::get('/{handbookId}/values/{handbookValueId}/edit', HandbookValueDetailController::class)
        ->name(HandbookValueRouteName::EDIT);
    Route::get('/{handbookId}/values/create', HandbookValueDetailController::class)
        ->name(HandbookValueRouteName::CREATE);

    Route::post('/{handbookId}/values', [HandbookValueDetailController::class, 'store'])
        ->name(HandbookValueRouteName::STORE);
    Route::post('/{handbookId}/values/{handbookValueId}', [HandbookValueDetailController::class, 'update'])
        ->name(HandbookValueRouteName::UPDATE);
    Route::post('/{handbookId}/values/{handbookValueId}/delete', [HandbookValueDetailController::class, 'delete'])
        ->name(HandbookValueRouteName::DELETE);
});

// Factions
Route::prefix('/factions')->group(static function (): void {
    Route::get('/', FactionIndexController::class)
        ->name(FactionRouteName::INDEX);
    Route::get('/{id}/edit', FactionDetailController::class)
        ->name(FactionRouteName::EDIT);
    Route::get('/create', FactionDetailController::class)
        ->name(FactionRouteName::CREATE);

    Route::post('/', [FactionDetailController::class, 'store'])
        ->name(FactionRouteName::STORE);
    Route::post('/{id}', [FactionDetailController::class, 'update'])
        ->name(FactionRouteName::UPDATE);
    Route::post('/{id}/delete', [FactionDetailController::class, 'delete'])
        ->name(FactionRouteName::DELETE);
    Route::post('/{id}/toggle-publish', [FactionDetailController::class, 'togglePublish'])
        ->name(FactionRouteName::TOGGLE_PUBLISH);
});

// Manufacturers
Route::prefix('/manufacturers')->group(static function (): void {
    Route::get('/', ManufacturerIndexController::class)
        ->name(ManufacturerRouteName::INDEX);
    Route::get('/{id}/edit', ManufacturerDetailController::class)
        ->name(ManufacturerRouteName::EDIT);
    Route::get('/create', ManufacturerDetailController::class)
        ->name(ManufacturerRouteName::CREATE);

    Route::post('/', [ManufacturerDetailController::class, 'store'])
        ->name(ManufacturerRouteName::STORE);
    Route::post('/{id}', [ManufacturerDetailController::class, 'update'])
        ->name(ManufacturerRouteName::UPDATE);
    Route::post('/{id}/delete', [ManufacturerDetailController::class, 'delete'])
        ->name(ManufacturerRouteName::DELETE);
    Route::post('/{id}/toggle-publish', [ManufacturerDetailController::class, 'togglePublish'])
        ->name(ManufacturerRouteName::TOGGLE_PUBLISH);
});

// Media
Route::prefix('/media')->group(static function () {
    Route::get('/', MediaIndexController::class)
        ->name(MediaRouteName::INDEX);
    Route::get('/{id}/edit', MediaDetailController::class)
        ->name(MediaRouteName::EDIT);
    Route::get('/create', MediaDetailController::class)
        ->name(MediaRouteName::CREATE);

    Route::post('/', [MediaDetailController::class, 'store'])
        ->name(MediaRouteName::STORE);
    Route::post('/{id}', [MediaDetailController::class, 'update'])
        ->name(MediaRouteName::UPDATE);
    Route::post('/{id}/delete', [MediaDetailController::class, 'delete'])
        ->name(MediaRouteName::DELETE);
    Route::post('/{id}/toggle-publish', [MediaDetailController::class, 'togglePublish'])
        ->name(MediaRouteName::TOGGLE_PUBLISH);
});

// Vehicles
Route::prefix('/vehicles')->group(static function () {
    Route::get('/', VehicleIndexController::class)
        ->name(VehicleRouteName::INDEX);
    Route::get('/{id}/edit', VehicleDetailController::class)
        ->name(VehicleRouteName::EDIT);
    Route::get('/create', VehicleDetailController::class)
        ->name(VehicleRouteName::CREATE);

    Route::post('/', [VehicleDetailController::class, 'store'])
        ->name(VehicleRouteName::STORE);
    Route::post('/{id}', [VehicleDetailController::class, 'update'])
        ->name(VehicleRouteName::UPDATE);
    Route::post('/{id}/delete', [VehicleDetailController::class, 'delete'])
        ->name(VehicleRouteName::DELETE);
    Route::post('/{id}/toggle-publish', [VehicleDetailController::class, 'togglePublish'])
        ->name(VehicleRouteName::TOGGLE_PUBLISH);

    Route::post('/{id}/page-settings/index', [VehicleDetailController::class, 'updateIndexPageSettings'])
        ->name(VehicleRouteName::UPDATE_INDEX_PAGE_SETTINGS);
    Route::post('/{id}/page-settings/detail', [VehicleDetailController::class, 'updateDetailPageSettings'])
        ->name(VehicleRouteName::UPDATE_DETAIL_PAGE_SETTINGS);
});

// Droids
Route::prefix('/droids')->group(static function () {
    Route::get('/', DroidIndexController::class)
        ->name(DroidRouteName::INDEX);
    Route::get('/{id}/edit', DroidDetailController::class)
        ->name(DroidRouteName::EDIT);
    Route::get('/create', DroidDetailController::class)
        ->name(DroidRouteName::CREATE);

    Route::post('/', [DroidDetailController::class, 'store'])
        ->name(DroidRouteName::STORE);
    Route::post('/{id}', [DroidDetailController::class, 'update'])
        ->name(DroidRouteName::UPDATE);
    Route::post('/{id}/delete', [DroidDetailController::class, 'delete'])
        ->name(DroidRouteName::DELETE);
    Route::post('/{id}/toggle-publish', [DroidDetailController::class, 'togglePublish'])
        ->name(DroidRouteName::TOGGLE_PUBLISH);

    Route::post('/{id}/page-settings/index', [DroidDetailController::class, 'updateIndexPageSettings'])
        ->name(DroidRouteName::UPDATE_INDEX_PAGE_SETTINGS);
    Route::post('/{id}/page-settings/detail', [DroidDetailController::class, 'updateDetailPageSettings'])
        ->name(DroidRouteName::UPDATE_DETAIL_PAGE_SETTINGS);
});
