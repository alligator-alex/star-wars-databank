<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Fields;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Vehicle\Public\Services\VehicleService;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class IndexPageSettings extends PageSettings
{
    protected $view = 'admin.common.fields.page-settings.index';

    private int $previewPageNum = 1;

    public function model(Vehicle|Droid $model): self
    {
        $this->model = $model;

        $serviceClass = match ($model::class) {
            Vehicle::class => VehicleService::class,
            Droid::class => DroidService::class,
            default => throw new RuntimeException('Unable to match service with model ' . $model::class),
        };

        /** @var VehicleService|DroidService $service */
        $service = app()->make($serviceClass);

        $counter = 1;

        /** @var Vehicle|Droid $item */
        foreach ($service->queryBuilder()->cursor() as $item) {
            if ($item->id !== $this->model->id) {
                $counter++;
                continue;
            }

            break;
        }

        $this->previewPageNum = (int) ceil($counter / $service::PER_PAGE);

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
            'previewPageNum' => $this->previewPageNum,
        ]));
    }
}
