<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers\Media;

use App\Modules\Core\Admin\Controllers\BaseEditScreen;
use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Components\Media\Layouts\Edit\InfoRows;
use App\Modules\Databank\Admin\Components\Media\Layouts\Edit\MainRows;
use App\Modules\Databank\Admin\Components\Media\Layouts\Edit\SystemLegend;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use App\Modules\Databank\Admin\Requests\Media\Create;
use App\Modules\Databank\Admin\Requests\Media\Update;
use App\Modules\Databank\Admin\Services\MediaService;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Media;
use BackedEnum;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Layout;
use Orchid\Support\Facades\Toast;

/**
 * @extends BaseEditScreen<Media>
 */
class EditScreen extends BaseEditScreen
{
    public function __construct(private readonly MediaService $service)
    {
    }

    protected function listRoute(): BackedEnum
    {
        return MediaRouteName::LIST;
    }

    protected function createRoute(): BackedEnum
    {
        return MediaRouteName::CREATE;
    }

    protected function updateRoute(): BackedEnum
    {
        return MediaRouteName::UPDATE;
    }

    protected function deleteRoute(): BackedEnum
    {
        return MediaRouteName::DELETE;
    }

    public function name(): ?string
    {
        if (is_null($this->model)) {
            return __('Not found');
        }

        if (!$this->model->exists) {
            return __('Create new Media');
        }

        if ($this->model->release_date) {
            return $this->model->name . ' (' . $this->model->releaseYear() . ')';
        }

        return $this->model->name;
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
            InfoRows::class,
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
