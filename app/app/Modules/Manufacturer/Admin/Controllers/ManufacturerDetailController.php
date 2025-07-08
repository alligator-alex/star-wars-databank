<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Admin\Controllers;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Controllers\BaseDetailController;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Manufacturer\Admin\Components\Layouts\Edit\MainRows;
use App\Modules\Manufacturer\Admin\Components\Layouts\Edit\SystemLegend;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Admin\Requests\StoreRequest;
use App\Modules\Manufacturer\Admin\Requests\UpdateRequest;
use App\Modules\Manufacturer\Admin\Services\ManufacturerService;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Layout;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;

/**
 * @extends BaseDetailController<Manufacturer>
 */
class ManufacturerDetailController extends BaseDetailController
{
    public function __construct(private readonly ManufacturerService $service)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function query(?int $id = null): iterable
    {
        try {
            if ($id > 0) {
                $this->model = $this->service->findOneById($id);
            } else {
                $this->model = $this->service->newModel();

                $this->model->status = Status::DRAFT;
                $this->model->sort = 500;
            }
        } catch (AdminServiceException) {
            return [];
        }

        return [
            'model' => $this->model,
        ];
    }

    /**
     * @return Layout[]|class-string[]
     */
    public function layout(): iterable
    {
        $rows = [
            MainRows::class,
        ];

        if ($this->model?->exists) {
            $rows[] = SystemLegend::class;
        }

        return $rows;
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $model = $this->service->create($request);

            Toast::success('"' . $model->name . '" ' . __('has been created'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        return redirect()->route(ManufacturerRouteName::UPDATE, ['id' => $model->id])->withInput();
    }

    public function update(int $id, UpdateRequest $request): RedirectResponse
    {
        try {
            $model = $this->service->update($id, $request);

            Toast::success('"' . $model->name . '" ' . __('has been updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        return redirect()->route(ManufacturerRouteName::UPDATE, ['id' => $model->id])->withInput();
    }

    public function delete(int $id): RedirectResponse
    {
        try {
            $model = $this->service->delete($id);

            Toast::success('"' . $model->name . '" ' . __('has been deleted'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        return redirect()->route(ManufacturerRouteName::INDEX);
    }

    public function togglePublish(int $id): RedirectResponse
    {
        try {
            $model = $this->service->togglePublish($id);

            Toast::success('"' . $model->name . '" ' . ($model->isPublished()
                ? __('has been published')
                : __('has been unpublished')));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }

        return redirect()->route(ManufacturerRouteName::INDEX);
    }
}
