<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers\Line;

use App\Modules\Core\Admin\Controllers\BaseEditScreen;
use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Components\Line\Layouts\Edit\MainRows;
use App\Modules\Databank\Admin\Components\Line\Layouts\Edit\SystemLegend;
use App\Modules\Databank\Admin\Enums\LineRouteName;
use App\Modules\Databank\Admin\Requests\Line\Create;
use App\Modules\Databank\Admin\Requests\Line\Update;
use App\Modules\Databank\Admin\Services\LineService;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Line;
use BackedEnum;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Layout;
use Orchid\Support\Facades\Toast;

/**
 * @extends BaseEditScreen<Line>
 */
class EditScreen extends BaseEditScreen
{
    public function __construct(private readonly LineService $service)
    {
    }

    protected function listRoute(): BackedEnum
    {
        return LineRouteName::LIST;
    }

    protected function createRoute(): BackedEnum
    {
        return LineRouteName::CREATE;
    }

    protected function updateRoute(): BackedEnum
    {
        return LineRouteName::UPDATE;
    }

    protected function deleteRoute(): BackedEnum
    {
        return LineRouteName::DELETE;
    }

    /**
     * @return array<string, mixed>
     */
    public function query(?int $id = null): iterable
    {
        try {
            if ($id > 0) {
                $this->model = $this->service->find($id);
            } else {
                $this->model = $this->service->getNewModel();

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

    public function create(Create $request): RedirectResponse
    {
        try {
            $model = $this->service->create($request);

            Toast::success('"' . $model->name . '" ' . __('has been created'));
        } catch (AdminServiceException $e) {
            return $this->handleException($e, $request);
        }

        return $this->redirectToListPage();
    }

    public function update(int $id, Update $request): RedirectResponse
    {
        try {
            $model = $this->service->update($id, $request);

            Toast::success('"' . $model->name . '" ' . __('has been updated'));
        } catch (AdminServiceException $e) {
            return $this->handleException($e, $request);
        }

        return $this->refreshPage();
    }

    public function delete(int $id): RedirectResponse
    {
        try {
            $model = $this->service->delete($id);

            Toast::success('"' . $model->name . '" ' . __('has been deleted'));
        } catch (AdminServiceException $e) {
            return $this->handleException($e);
        }

        return $this->redirectToListPage();
    }

    public function togglePublish(int $id): RedirectResponse
    {
        try {
            $model = $this->service->togglePublish($id);

            Toast::success('"' . $model->name . '" '
                . ($model->isPublished() ? __('has been published') : __('has been unpublished')));
        } catch (AdminServiceException $e) {
            Toast::error($e->getMessage());
        }

        return $this->redirectToListPage();
    }
}
