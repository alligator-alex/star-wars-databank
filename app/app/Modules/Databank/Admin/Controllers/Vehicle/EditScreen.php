<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers\Vehicle;

use App\Modules\Core\Admin\Controllers\BaseEditScreen;
use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Components\Vehicle\Fields\DetailPageSettings;
use App\Modules\Databank\Admin\Components\Vehicle\Fields\ListPageSettings;
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit\AppearancesRows;
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit\InfoRows;
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit\MainRows;
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit\SystemLegend;
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit\TechSpecsRows;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Admin\Requests\Vehicle\Create;
use App\Modules\Databank\Admin\Requests\Vehicle\UpdateDetailPageSettings;
use App\Modules\Databank\Admin\Requests\Vehicle\UpdateListPageSettings;
use App\Modules\Databank\Admin\Requests\Vehicle\Update;
use App\Modules\Databank\Admin\Services\VehicleService;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Vehicle;
use BackedEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layout;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout as LayoutFacade;
use Orchid\Support\Facades\Toast;

/**
 * @extends BaseEditScreen<Vehicle>
 */
class EditScreen extends BaseEditScreen
{
    public function __construct(private readonly VehicleService $service)
    {
    }

    protected function listRoute(): BackedEnum
    {
        return VehicleRouteName::LIST;
    }

    protected function createRoute(): BackedEnum
    {
        return VehicleRouteName::CREATE;
    }

    protected function updateRoute(): BackedEnum
    {
        return VehicleRouteName::UPDATE;
    }

    protected function deleteRoute(): BackedEnum
    {
        return VehicleRouteName::DELETE;
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

    public function commandBar(): array
    {
        if (!$this->model || !$this->model->exists) {
            return parent::commandBar();
        }

        return array_merge([
            ModalToggle::make(__('Edit list page settings'))
                ->modal('listPageSettingsModal')
                ->route(VehicleRouteName::UPDATE_LIST_PAGE_SETTINGS->value, $this->model->getAttribute('id'), false)
                ->icon('bs.grid'),

            ModalToggle::make(__('Edit detail page settings'))
                ->modal('detailPageSettingsModal')
                ->route(VehicleRouteName::UPDATE_ONE_PAGE_SETTINGS->value, $this->model->getAttribute('id'), false)
                ->icon('bs.view-list'),
        ], parent::commandBar());
    }

    /**
     * @return Layout[]|class-string[]
     */
    public function layout(): iterable
    {
        $rows = [
            MainRows::class,
            InfoRows::class,
            TechSpecsRows::class,
            AppearancesRows::class,
        ];

        if ($this->model?->exists) {
            $rows = array_merge($rows, [
                SystemLegend::class,

                LayoutFacade::modal(
                    'listPageSettingsModal',
                    LayoutFacade::rows([
                        ListPageSettings::make('list')
                            ->vehicle($this->model),
                    ]),
                )->title(__('List page settings'))
                    ->size(Modal::SIZE_XL)
                    ->applyButton(__('Save')),

                LayoutFacade::modal(
                    'detailPageSettingsModal',
                    LayoutFacade::rows([
                        DetailPageSettings::make('detail')
                            ->vehicle($this->model),
                    ]),
                )->title(__('Detail page settings'))
                    ->size(Modal::SIZE_XL)
                    ->applyButton(__('Save')),
            ]);
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

    public function togglePublish(int $id, Request $request): RedirectResponse
    {
        try {
            $model = $this->service->togglePublish($id);

            Toast::success('"' . $model->name . '" '
                . ($model->isPublished() ? __('has been published') : __('has been unpublished')));
        } catch (AdminServiceException $e) {
            Toast::error($e->getMessage());
        }

        return $this->redirectToListPage($request->query());
    }

    public function updateListPageSettings(int $id, UpdateListPageSettings $request): void
    {
        try {
            $this->service->updateListPageSettings($id, $request);
            Toast::success(__('List page settings has been updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }
    }

    public function updateOnePageSettings(int $id, UpdateDetailPageSettings $request): void
    {
        try {
            $this->service->updateDetailPageSettings($id, $request);
            Toast::success(__('Detail page settings has been updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }
    }
}
