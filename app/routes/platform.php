<?php

declare(strict_types=1);

use App\Modules\Core\Admin\Controllers\PlatformScreen;
use App\Modules\Core\Admin\Controllers\User\ProfileScreen;
use App\Modules\Core\Admin\Enums\AdminRouteName;
use App\Modules\Core\Admin\Enums\UserRouteName;
use App\Modules\Databank\Admin\Controllers as DatabankControllers;
use App\Modules\Databank\Admin\Enums\FactionRouteName;
use App\Modules\Databank\Admin\Enums\LineRouteName;
use App\Modules\Databank\Admin\Enums\ManufacturerRouteName;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

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

// Main
Route::screen('/databank', PlatformScreen::class)
    ->name(AdminRouteName::HOME)
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->push(__('Dashboard'), route(name: AdminRouteName::HOME, absolute: false)));

// Profile
Route::prefix('/profile')->group(static function () {
    Route::get('/', ProfileScreen::class)
        ->name(UserRouteName::PROFILE)
        ->breadcrumbs(static fn (Trail $trail) => $trail
            ->parent(AdminRouteName::HOME->value)
            ->push(__('Profile'), route(name: UserRouteName::PROFILE, absolute: false)));

    Route::post('/', [ProfileScreen::class, 'update'])
        ->name(UserRouteName::UPDATE);

    Route::post('/change-password', [ProfileScreen::class, 'changePassword'])
        ->name(UserRouteName::CHANGE_PASSWORD);
});

// Factions
Route::prefix('/factions')->group(static function () {
    Route::get('/', DatabankControllers\Faction\ListScreen::class)
        ->name(FactionRouteName::LIST)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(AdminRouteName::HOME->value)
            ->push(__('Factions'), FactionRouteName::LIST->value));

    Route::post('/create', [DatabankControllers\Faction\EditScreen::class, 'create'])
        ->name(FactionRouteName::CREATE);

    Route::get('/create', DatabankControllers\Faction\EditScreen::class)
        ->name(FactionRouteName::NEW)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(FactionRouteName::LIST->value)
            ->push(__('Create')));

    Route::post('/{id}', [DatabankControllers\Faction\EditScreen::class, 'update'])
        ->name(FactionRouteName::UPDATE);

    Route::get('/{id}', DatabankControllers\Faction\EditScreen::class)
        ->name(FactionRouteName::ONE)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(FactionRouteName::LIST->value)
            ->push(__('Edit')));

    Route::post('/{id}/delete', [DatabankControllers\Faction\EditScreen::class, 'delete'])
        ->name(FactionRouteName::DELETE);

    Route::post('/{id}/toggle-publish', [DatabankControllers\Faction\EditScreen::class, 'togglePublish'])
        ->name(FactionRouteName::TOGGLE_PUBLISH);
});

// Lines
Route::prefix('/lines')->group(static function () {
    Route::get('/', DatabankControllers\Line\ListScreen::class)
        ->name(LineRouteName::LIST)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(AdminRouteName::HOME->value)
            ->push(__('Lines'), LineRouteName::LIST->value));

    Route::post('/create', [DatabankControllers\Line\EditScreen::class, 'create'])
        ->name(LineRouteName::CREATE);

    Route::get('/create', DatabankControllers\Line\EditScreen::class)
        ->name(LineRouteName::NEW)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(LineRouteName::LIST->value)
            ->push(__('Create')));

    Route::post('/{id}', [DatabankControllers\Line\EditScreen::class, 'update'])
        ->name(LineRouteName::UPDATE);

    Route::get('/{id}', DatabankControllers\Line\EditScreen::class)
        ->name(LineRouteName::ONE)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(LineRouteName::LIST->value)
            ->push(__('Edit')));

    Route::post('/{id}/delete', [DatabankControllers\Line\EditScreen::class, 'delete'])
        ->name(LineRouteName::DELETE);

    Route::post('/{id}/toggle-publish', [DatabankControllers\Line\EditScreen::class, 'togglePublish'])
        ->name(LineRouteName::TOGGLE_PUBLISH);
});

// Manufacturers
Route::prefix('/manufacturers')->group(static function () {
    Route::get('/', DatabankControllers\Manufacturer\ListScreen::class)
        ->name(ManufacturerRouteName::LIST)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(AdminRouteName::HOME->value)
            ->push(__('Manufacturers'), ManufacturerRouteName::LIST->value));

    Route::post('/create', [DatabankControllers\Manufacturer\EditScreen::class, 'create'])
        ->name(ManufacturerRouteName::CREATE);

    Route::get('/create', DatabankControllers\Manufacturer\EditScreen::class)
        ->name(ManufacturerRouteName::NEW)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(ManufacturerRouteName::LIST->value)
            ->push(__('Create')));

    Route::post('/{id}', [DatabankControllers\Manufacturer\EditScreen::class, 'update'])
        ->name(ManufacturerRouteName::UPDATE);

    Route::get('/{id}', DatabankControllers\Manufacturer\EditScreen::class)
        ->name(ManufacturerRouteName::ONE)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(ManufacturerRouteName::LIST->value)
            ->push(__('Edit')));

    Route::post('/{id}/delete', [DatabankControllers\Manufacturer\EditScreen::class, 'delete'])
        ->name(ManufacturerRouteName::DELETE);

    Route::post('/{id}/toggle-publish', [DatabankControllers\Manufacturer\EditScreen::class, 'togglePublish'])
        ->name(ManufacturerRouteName::TOGGLE_PUBLISH);
});

// Vehicles
Route::prefix('/vehicles')->group(static function () {
    Route::get('/', DatabankControllers\Vehicle\ListScreen::class)
        ->name(VehicleRouteName::LIST)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(AdminRouteName::HOME->value)
            ->push(__('Vehicles'), VehicleRouteName::LIST->value));

    Route::post('/create', [DatabankControllers\Vehicle\EditScreen::class, 'create'])
        ->name(VehicleRouteName::CREATE);

    Route::get('/create', DatabankControllers\Vehicle\EditScreen::class)
        ->name(VehicleRouteName::NEW)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(VehicleRouteName::LIST->value)
            ->push(__('Create')));

    Route::post('/{id}', [DatabankControllers\Vehicle\EditScreen::class, 'update'])
        ->name(VehicleRouteName::UPDATE);

    Route::get('/{id}', DatabankControllers\Vehicle\EditScreen::class)
        ->name(VehicleRouteName::ONE)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(VehicleRouteName::LIST->value)
            ->push(__('Edit')));

    Route::post('/{id}/delete', [DatabankControllers\Vehicle\EditScreen::class, 'delete'])
        ->name(VehicleRouteName::DELETE);

    Route::post('/{id}/toggle-publish', [DatabankControllers\Vehicle\EditScreen::class, 'togglePublish'])
        ->name(VehicleRouteName::TOGGLE_PUBLISH);

    Route::post('/{id}/page-settings/list', [DatabankControllers\Vehicle\EditScreen::class, 'updateListPageSettings'])
        ->name(VehicleRouteName::UPDATE_LIST_PAGE_SETTINGS);

    Route::post('/{id}/page-settings/one', [DatabankControllers\Vehicle\EditScreen::class, 'updateOnePageSettings'])
        ->name(VehicleRouteName::UPDATE_ONE_PAGE_SETTINGS);
});

// Media
Route::prefix('/media')->group(static function () {
    Route::get('/', DatabankControllers\Media\ListScreen::class)
        ->name(MediaRouteName::LIST)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(AdminRouteName::HOME->value)
            ->push(__('Media'), MediaRouteName::LIST->value));

    Route::post('/create', [DatabankControllers\Media\EditScreen::class, 'create'])
        ->name(MediaRouteName::CREATE);

    Route::get('/create', DatabankControllers\Media\EditScreen::class)
        ->name(MediaRouteName::NEW)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(MediaRouteName::LIST->value)
            ->push(__('Create')));

    Route::post('/{id}', [DatabankControllers\Media\EditScreen::class, 'update'])
        ->name(MediaRouteName::UPDATE);

    Route::get('/{id}', DatabankControllers\Media\EditScreen::class)
        ->name(MediaRouteName::ONE)
        ->breadcrumbs(static fn (Trail $trail): Trail => $trail
            ->parent(MediaRouteName::LIST->value)
            ->push(__('Edit')));

    Route::post('/{id}/delete', [DatabankControllers\Media\EditScreen::class, 'delete'])
        ->name(MediaRouteName::DELETE);

    Route::post('/{id}/toggle-publish', [DatabankControllers\Media\EditScreen::class, 'togglePublish'])
        ->name(MediaRouteName::TOGGLE_PUBLISH);
});
