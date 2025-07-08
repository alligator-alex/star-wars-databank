<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Controllers;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Controllers\BaseDetailController;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Admin\Components\Layouts\Edit\MainRows;
use App\Modules\Faction\Admin\Components\Layouts\Edit\SystemLegend;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Admin\Requests\StoreRequest;
use App\Modules\Faction\Admin\Requests\UpdateRequest;
use App\Modules\Faction\Admin\Services\FactionService;
use App\Modules\Faction\Common\Models\Faction;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Layout;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;

/**
 * @extends BaseDetailController<Faction>
 */
class FactionDetailController extends BaseDetailController
{
    public function __construct(private readonly FactionService $service)
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

        return redirect()->route(FactionRouteName::EDIT, ['id' => $model->id])->withInput();
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

        return redirect()->route(FactionRouteName::EDIT, ['id' => $model->id])->withInput();
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

        return redirect()->route(FactionRouteName::INDEX);
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

        return redirect()->route(FactionRouteName::INDEX);
    }
}
