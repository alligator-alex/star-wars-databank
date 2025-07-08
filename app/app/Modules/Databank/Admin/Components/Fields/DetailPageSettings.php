<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Fields;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Throwable;

class DetailPageSettings extends PageSettings
{
    protected $view = 'admin.common.fields.page-settings.detail';

    public function model(Vehicle|Droid $model): self
    {
        $this->model = $model;

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
            'model' => $this->model,
        ]));
    }
}
