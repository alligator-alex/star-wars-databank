<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Fields;

use App\Modules\Databank\Common\Models\Vehicle;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Throwable;

class DetailPageSettings extends PageSettings
{
    protected $view = 'admin.databank.vehicles.fields.detail-page-settings';

    public function vehicle(Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

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
        ]));
    }
}
