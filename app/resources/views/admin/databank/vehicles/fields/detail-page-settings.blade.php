@php
use Orchid\Screen\Fields\Input;
use App\Modules\Databank\Public\Enums\VehicleRouteName;

/**
 * @var App\Modules\Databank\Common\Models\Vehicle $vehicle
 */

$controllerName = 'vehicle-detail-page-settings';

$settings = $vehicle->getPageSettings()->getForDetail();
@endphp
<div data-controller="{{ $controllerName }}">
    <div class="d-flex flex-column gap-3">
        <div class="row form-group align-items-baseline">
            <legend class="text-black">{{ __('Image') }}</legend>
            <div class="col-sm-4">
                {!!
                Input::make('imageOffsetX')
                    ->title(__('Offset X'))
                    ->type('range')
                    ->class('form-range')
                    ->min(0)
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
                Input::make('imageOffsetY')
                    ->title(__('Offset Y'))
                    ->type('range')
                    ->class('form-range')
                    ->min(0)
                    ->max(100)
                    ->step(1)
                    ->value($settings->getImageOffsetY())
                    ->set('data-' . $controllerName . '-target', 'imageOffsetY')
                    ->set('data-action', 'input->' . $controllerName . '#changeImageOffset')
                    ->render();
                !!}
            </div>
            <div class="col-sm-4">
                {!!
                Input::make('imageMaxHeight')
                    ->title(__('Max height'))
                    ->type('range')
                    ->class('form-range')
                    ->min(1)
                    ->max(100)
                    ->step(1)
                    ->value($settings->getImageMaxHeight())
                    ->set('data-' . $controllerName . '-target', 'imageMaxHeight')
                    ->set('data-action', 'input->' . $controllerName . '#changeImageMaxHeight')
                    ->render();
                !!}
            </div>
        </div>

        <div class="row">
            <legend class="text-black">{{ __('Preview') }}</legend>
            @if (!$vehicle->isPublished())
                <div>
                    <div class="alert alert-warning rounded shadow-sm mb-3 p-4 d-flex">
                        {{ __('This vehicle is not published yet!') }}
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif
            @include('admin.databank.vehicles.fields.partials.page-preview', [
                'publicPageUrl' => route(VehicleRouteName::ONE, $vehicle->slug, false),
                'class' => 'js-detail-page-preview',
            ])
        </div>
    </div>
</div>
