<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Fields;

use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Public\Services\VehicleService;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Throwable;

class ListPageSettings extends PageSettings
{
    protected $view = 'admin.databank.vehicles.fields.list-page-settings';

    private int $previewPageNum = 1;

    public function vehicle(Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        $publicService = app()->make(VehicleService::class);

        $counter = 1;

        /** @var Vehicle $publishedVehicle */
        foreach ($publicService->allQuery()->cursor() as $publishedVehicle) {
            if ($publishedVehicle->id !== $this->vehicle->id) {
                $counter++;
                continue;
            }

            break;
        }

        $this->previewPageNum = (int) ceil($counter / VehicleService::PER_PAGE);

        return $this;
    }

    /**
     * Renders the field.
     *
     * @throws Throwable
     *
     * @return Factory|View|mixed|void
     */
    public function render()
    {
        if (!$this->isSee()) {
            return;
        }

        $this
            ->ensureRequiredAttributesArePresent()
            ->customizeFieldName()
            ->updateFieldValue()
            ->runBeforeRender()
            ->translateAttributes()
            ->markFieldWithError()
            ->generateId();

        return view($this->view, array_merge($this->getAttributes(), [
            'vehicle' => $this->vehicle,
            'previewPageNum' => $this->previewPageNum,
        ]));
    }
}
