<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Controllers;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Components\Fields\DetailPageSettings;
use App\Modules\Databank\Admin\Components\Fields\IndexPageSettings;
use App\Modules\Databank\Admin\Controllers\BaseDetailController;
use App\Modules\Databank\Admin\Requests\UpdateDetailPageSettingsRequest;
use App\Modules\Databank\Admin\Requests\UpdateIndexPageSettingsRequest;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Admin\Services\FactionService;
use App\Modules\Handbook\Admin\Services\HandbookValueService;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Manufacturer\Admin\Services\ManufacturerService;
use App\Modules\Media\Admin\Services\MediaService;
use App\Modules\Vehicle\Admin\Components\Layouts\Edit\AppearancesRows;
use App\Modules\Vehicle\Admin\Components\Layouts\Edit\InfoRows;
use App\Modules\Vehicle\Admin\Components\Layouts\Edit\MainRows;
use App\Modules\Vehicle\Admin\Components\Layouts\Edit\SystemLegend;
use App\Modules\Vehicle\Admin\Components\Layouts\Edit\TechSpecsRows;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Admin\Requests\StoreRequest;
use App\Modules\Vehicle\Admin\Requests\UpdateRequest;
use App\Modules\Vehicle\Admin\Services\VehicleService;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layout;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout as LayoutFacade;
use Orchid\Support\Facades\Toast;

/**
 * @extends BaseDetailController<Vehicle>
 */
class VehicleDetailController extends BaseDetailController
{
    public function __construct(
        private readonly VehicleService $service,
        private readonly ManufacturerService $manufacturerService,
        private readonly FactionService $factionService,
        private readonly MediaService $mediaService,
        private readonly HandbookValueService $handbookValueService
    ) {
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

    public function commandBar(): array
    {
        if (!$this->model || !$this->model->exists) {
            return parent::commandBar();
        }

        return array_merge([
            ModalToggle::make(__('Edit index page settings'))
                ->modal('indexPageSettingsModal')
                ->route(VehicleRouteName::UPDATE_INDEX_PAGE_SETTINGS->value, $this->model->getKey(), false)
                ->icon('bs.grid'),

            ModalToggle::make(__('Edit detail page settings'))
                ->modal('detailPageSettingsModal')
                ->route(VehicleRouteName::UPDATE_DETAIL_PAGE_SETTINGS->value, $this->model->getKey(), false)
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
            new InfoRows(
                $this->handbookValueService->dropdownList(HandbookType::VEHICLE_CATEGORY),
                $this->handbookValueService->dropdownList(HandbookType::VEHICLE_TYPE),
                $this->handbookValueService->dropdownList(HandbookType::VEHICLE_LINE),
                $this->manufacturerService->dropdownList(),
                $this->factionService->dropdownList()
            ),
            TechSpecsRows::class,
            new AppearancesRows($this->mediaService->dropdownList()),
        ];

        if ($this->model?->exists) {
            $rows = array_merge($rows, [
                SystemLegend::class,

                LayoutFacade::modal(
                    'indexPageSettingsModal',
                    LayoutFacade::rows([
                        IndexPageSettings::make('index')
                            ->model($this->model),
                    ]),
                )->title(__('Index page settings'))
                    ->size(Modal::SIZE_XL)
                    ->applyButton(__('Save')),

                LayoutFacade::modal(
                    'detailPageSettingsModal',
                    LayoutFacade::rows([
                        DetailPageSettings::make('detail')
                            ->model($this->model),
                    ]),
                )->title(__('Detail page settings'))
                    ->size(Modal::SIZE_XL)
                    ->applyButton(__('Save')),
            ]);
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

        return redirect()->route(VehicleRouteName::EDIT, ['id' => $model->id])->withInput();
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

        return redirect()->route(VehicleRouteName::EDIT, ['id' => $model->id])->withInput();
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

        return redirect()->route(VehicleRouteName::INDEX);
    }

    public function togglePublish(int $id, Request $request): RedirectResponse
    {
        try {
            $model = $this->service->togglePublish($id);

            Toast::success('"' . $model->name . '" ' . ($model->isPublished()
                ? __('has been published')
                : __('has been unpublished')));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }

        return redirect()->route(VehicleRouteName::INDEX, $request->query());
    }

    public function updateIndexPageSettings(int $id, UpdateIndexPageSettingsRequest $request): RedirectResponse
    {
        try {
            $this->service->updateIndexPageSettings($id, $request);
            Toast::success(__('Index page settings has been updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }

        return redirect()->route(VehicleRouteName::EDIT, ['id' => $id]);
    }

    public function updateDetailPageSettings(int $id, UpdateDetailPageSettingsRequest $request): RedirectResponse
    {
        try {
            $this->service->updateDetailPageSettings($id, $request);
            Toast::success(__('Detail page settings has been updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }

        return redirect()->route(VehicleRouteName::EDIT, ['id' => $id]);
    }
}
