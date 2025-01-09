<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Providers;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Core\Common\Models\User;
use App\Modules\Databank\Admin\Enums\FactionRouteName;
use App\Modules\Databank\Admin\Enums\LineRouteName;
use App\Modules\Databank\Admin\Enums\ManufacturerRouteName;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use Orchid\Attachment\Models\Attachment as OrchidAttachment;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\Models\User as OrchidUser;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class AdminServiceProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param Dashboard $dashboard
     */
    public function boot(Dashboard $dashboard): void
    {
        Dashboard::useModel(OrchidUser::class, User::class);
        Dashboard::useModel(OrchidAttachment::class, Attachment::class);

        parent::boot($dashboard);
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make(__('Vehicles'))
                ->route(VehicleRouteName::LIST->value),

            Menu::make(__('Factions'))
                ->route(FactionRouteName::LIST->value),

            Menu::make(__('Manufacturers'))
                ->route(ManufacturerRouteName::LIST->value),

            Menu::make(__('Lines'))
                ->route(LineRouteName::LIST->value),

            Menu::make(__('Media'))
                ->route(MediaRouteName::LIST->value)
                ->divider(),

            Menu::make('Wookieepedia home page')
                ->icon('bs.box-arrow-up-right')
                ->url('https://starwars.fandom.com/wiki/Main_Page')
                ->target('_blank')
                ->divider(),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
