@php
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;

/**
 * @var Vehicle|Droid $model
 * @var int $previewPageNum
 */

$controllerName = 'entity-index-page-settings';

$settings = $model->getPageSettings()->getForIndex();

$routeName = match ($model::class) {
    Vehicle::class => VehicleRouteName::INDEX,
    Droid::class => DroidRouteName::INDEX,
}
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
                    ->value($settings->isImageScaled())
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
                    ->disabled($settings->isImageScaled() === false)
                    ->type('range')
                    ->class('form-range')
                    ->min(0.1)
                    ->max(2)
                    ->step(0.1)
                    ->value($settings->isImageScaled() ? $settings->getImageScale() : 1.0)
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
            @if (!$model->isPublished())
                <div>
                    <div class="alert alert-warning rounded shadow-sm mb-3 p-4 d-flex">
                        {{ __('You must publish this :model first to see changes!', ['model' => class_basename($model)]) }}
                    </div>
                </div>
            @else
                @include('admin.common.partials.page-preview', [
                    'publicPageUrl' => route($routeName, [
                        'page' => $previewPageNum,
                        'preview-target' => $model->id,
                    ], false),
                    'class' => 'js-index-page-preview',
                ])
            @endif
        </div>
    </div>
</div>
