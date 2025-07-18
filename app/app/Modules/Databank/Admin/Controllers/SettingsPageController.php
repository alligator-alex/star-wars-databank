<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers;

use App\Modules\Core\Admin\Enums\AdminRouteName;
use Illuminate\Cache\RedisStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Tabuna\Breadcrumbs\Breadcrumbs;
use Tabuna\Breadcrumbs\Trail;

class SettingsPageController extends Screen
{
    /**
     * @return array<int, null>
     */
    public function query(): iterable
    {
        return [];
    }

    public function name(): ?string
    {
        return __('Settings');
    }

    public function clearCache(): RedirectResponse
    {
        Cache::flush();
        Toast::success(__('Cache cleared'));

        return redirect()->to(route(AdminRouteName::SETTINGS, absolute: false));
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        Breadcrumbs::for(
            AdminRouteName::SETTINGS->value,
            static fn (Trail $trail) => $trail
                ->push(__('Settings'), route(name: AdminRouteName::SETTINGS, absolute: false))
        );

        return [
            Layout::rows([
                TextArea::make()
                    ->title(__('Cache keys'))
                    ->rows(10)
                    ->value(implode(PHP_EOL, $this->cacheKeys()))
                    ->readonly()
                    ->style('font-family: monospace; color: black; max-width: 100%'),

                Button::make(__('Clear all cache'))
                    ->route(AdminRouteName::CLEAR_CACHE->value, absolute: false)
                    ->confirm(__('Are you sure you want to clear the cache?'))
                    ->icon('bs.radioactive')
                    ->type(Color::DANGER),
            ])->title(__('Cache')),
        ];
    }

    private function cacheKeys(): array
    {
        /** @var RedisStore $store */
        $store = Cache::getStore();
        $client = $store->connection()->client();

        $cacheKeys = [];

        // don't forget it must be null for first call
        $iterator = null;
        while ($keys = $client->scan($iterator, '*', 100)) {
            foreach ($keys as $key) {
                $cacheKeys[] = $key;
            }
        }

        return $cacheKeys;
    }
}
