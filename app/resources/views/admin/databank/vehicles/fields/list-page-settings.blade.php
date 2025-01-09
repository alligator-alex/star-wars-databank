@php
    use App\Modules\Databank\Public\Enums\VehicleRouteName;

    /**
     * @var App\Modules\Databank\Common\Models\Vehicle $vehicle
     * @var int $previewPageNum
     */

    $controllerName = 'vehicle-list-page-settings';

    $settings = $vehicle->getPageSettings()->getForList();
@endphp
<div data-controller="{{ $controllerName }}">
    <div class="d-flex flex-column gap-3">
        <div class="row form-group align-items-baseline">
            <div class="col-sm-2">
                {!!
                Orchid\Screen\Fields\Switcher::make('cardLarge')
                    ->title(__('Use large card'))
                    ->value($settings->isCardLarge())
                    ->sendTrueOrFalse()
                    ->set('data-' . $controllerName . '-target', 'cardLargeToggle')
                    ->set('data-action', 'input->' . $controllerName . '#toggleCardLarge')
                    ->render();
                !!}
            </div>
            <div class="col-sm-2">
                {!!
                Orchid\Screen\Fields\Switcher::make('imageCovered')
                    ->title(__('Cover image'))
                    ->value($settings->isImageCovered())
                    ->sendTrueOrFalse()
                    ->set('data-' . $controllerName . '-target', 'imageCoverToggle')
                    ->set('data-action', 'input->' . $controllerName . '#toggleImageCover')
                    ->render();
                !!}
            </div>
        </div>
        <div class="row form-group align-items-baseline">
            <div class="col-sm-2">
                {!!
                Orchid\Screen\Fields\Switcher::make('imageScaled')
                    ->title(__('Scale image'))
                    ->value($settings->isImageCovered())
                    ->sendTrueOrFalse()
                    ->set('data-' . $controllerName . '-target', 'imageScaleToggle')
                    ->set('data-action', 'input->' . $controllerName . '#toggleImageScale')
                    ->render();
                !!}
            </div>
            <div class="col-sm-2">
                {!!
                Orchid\Screen\Fields\Switcher::make('imageOffsetted')
                    ->title(__('Offset image'))
                    ->value($settings->isImageOffsetted())
                    ->sendTrueOrFalse()
                    ->set('data-' . $controllerName . '-target', 'imageOffsetToggle')
                    ->set('data-action', 'input->' . $controllerName . '#toggleImageOffset')
                    ->render();
                !!}
            </div>
        </div>
        <div class="row form-group align-items-baseline">
            <legend class="text-black">{{ __('Image') }}</legend>
            <div class="col-sm-4">
                {!!
                Orchid\Screen\Fields\Input::make('imageScale')
                    ->title(__('Scale'))
                    ->disabled($settings->isImageCovered() === false)
                    ->type('range')
                    ->class('form-range')
                    ->min(0.1)
                    ->max(2)
                    ->step(0.1)
                    ->value($settings->isImageCovered() ? $settings->getImageScale() : 1.0)
                    ->set('data-' . $controllerName . '-target', 'imageScale')
                    ->set('data-action', 'input->' . $controllerName . '#changeImageScale')
                    ->render();
                !!}
            </div>
            <div class="col-sm-4">
                {!!
                Orchid\Screen\Fields\Input::make('imageOffsetX')
                    ->title(__('Offset X'))
                    ->disabled($settings->isImageOffsetted() === false)
                    ->type('range')
                    ->class('form-range')
                    ->min(-100)
                    ->max(100)
                    ->step(1)
                    ->value($settings->getImageOffsetX())
                    ->set('data-' . $controllerName . '-target', 'imageOffsetX')
                    ->set('data-action', 'input->' . $controllerName . '#changeImageOffset')
                    ->render();
                !!}
            </div>
            <div class="col-sm-4">
                {!!
                Orchid\Screen\Fields\Input::make('imageOffsetY')
                    ->title(__('Offset Y'))
                    ->disabled($settings->isImageOffsetted() === false)
                    ->type('range')
                    ->class('form-range')
                    ->min(-100)
                    ->max(100)
                    ->step(1)
                    ->value($settings->getImageOffsetY())
                    ->set('data-' . $controllerName . '-target', 'imageOffsetY')
                    ->set('data-action', 'input->' . $controllerName . '#changeImageOffset')
                    ->render();
                !!}
            </div>
        </div>

        <div class="row">
            <legend class="text-black">Preview</legend>
            @if (!$vehicle->isPublished())
                <div>
                    <div class="alert alert-warning rounded shadow-sm mb-3 p-4 d-flex">
                        {{ __('You must publish this vehicle first to see changes!') }}
                    </div>
                </div>
            @else
                @include('admin.databank.vehicles.fields.partials.page-preview', [
                    'publicPageUrl' => route(VehicleRouteName::LIST, [
                            'page' => $previewPageNum,
                            'preview-target' => $vehicle->id,
                        ], false),
                    'class' => 'js-list-page-preview',
                ])
            @endif
        </div>
    </div>
</div>
