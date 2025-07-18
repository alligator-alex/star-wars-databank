<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Providers;

use App\Modules\Core\Admin\Enums\AdminRouteName;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Core\Common\Models\User;
use App\Modules\Databank\Import\Contracts\Importer;
use App\Modules\Databank\Import\Contracts\Parser;
use App\Modules\Databank\Import\Importer\WookieepediaImporter;
use App\Modules\Databank\Import\ImportLogger;
use App\Modules\Databank\Import\Parser\WookiepediaParser;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Orchid\Attachment\Models\Attachment as OrchidAttachment;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\Models\User as OrchidUser;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class DatabankServiceProvider extends OrchidServiceProvider
{
    /**
     * @return class-string[]
     */
    public function provides(): array
    {
        return [
            Parser::class,
            Importer::class,
        ];
    }

    public function register(): void
    {
        $this->app->bind(Parser::class, static fn (): Parser => new WookiepediaParser(new ImportLogger()));

        $this->app->bind(Importer::class, static fn (): Importer => new WookieepediaImporter(
            new ImportLogger(),
            new FactionRepository(),
            new ManufacturerRepository(),
            new MediaRepository(),
            new HandbookRepository(),
            new HandbookValueRepository()
        ));
    }

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

    /**
     * Register the application menu.
     *
     * @return Menu[]
     *
     * @throws BindingResolutionException
     */
    public function menu(): array
    {
        return [
            Menu::make(__('Vehicles'))
                ->icon('bs.bicycle')
                ->route(VehicleRouteName::INDEX->value),

            Menu::make(__('Droids'))
                ->icon('bs.outlet')
                ->route(DroidRouteName::INDEX->value)
                ->divider(),

            Menu::make(__('Handbooks'))
                ->list($this->handbookMenuItems())
                ->icon('bs.book')
                ->divider(),

            Menu::make(__('Factions'))
                ->icon('bs.hurricane')
                ->route(FactionRouteName::INDEX->value),

            Menu::make(__('Manufacturers'))
                ->icon('bs.tools')
                ->route(ManufacturerRouteName::INDEX->value),

            Menu::make(__('Media'))
                ->icon('bs.camera-video')
                ->route(MediaRouteName::INDEX->value)
                ->divider(),

            Menu::make(__('Settings'))
                ->icon('bs.gear')
                ->route(AdminRouteName::SETTINGS->value)
                ->divider(),

            Menu::make(__('Wookieepedia home page'))
                ->icon('bs.box-arrow-up-right')
                ->url('https://starwars.fandom.com/wiki/Main_Page')
                ->target('_blank'),
        ];
    }

    /**
     * @return Menu[]
     *
     * @throws BindingResolutionException
     */
    private function handbookMenuItems(): array
    {
        /** @var HandbookRepository $handbookRepository */
        $handbookRepository = $this->app->make(HandbookRepository::class);

        /** @var Collection<int, Handbook> $handbooks */
        $handbooks = $handbookRepository->queryBuilder()
            ->orderBy('type')
            ->get();

        $handbooksMenuItems = [];
        foreach ($handbooks as $handbook) {
            $handbooksMenuItems[] = Menu::make($handbook->name)
                ->route(HandbookValueRouteName::INDEX->value, [
                    'handbookId' => $handbook->id,
                ]);
        }

        return $handbooksMenuItems;
    }
}
