<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Controllers;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Handbook\Admin\Components\Layouts\Edit\MainRows;
use App\Modules\Handbook\Admin\Components\Layouts\Edit\SystemLegend;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Admin\Requests\StoreRequest;
use App\Modules\Handbook\Admin\Requests\UpdateRequest;
use App\Modules\Handbook\Admin\Services\HandbookValueService;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layout;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;

class HandbookValueDetailController extends Screen
{
    private ?Handbook $handbook = null;
    private ?HandbookValue $handbookValue = null;

    public function __construct(
        private readonly HandbookRepository $handbookRepository,
        private readonly HandbookValueService $service,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function query(int $handbookId, ?int $handbookValueId = null): iterable
    {
        $this->handbook = $this->handbookRepository->findOneById($handbookId);

        try {
            if ($handbookValueId > 0) {
                $this->handbookValue = $this->service->findOneByIdOrFail($handbookId, $handbookValueId);
            } else {
                $this->handbookValue = $this->service->newModel();
            }
        } catch (AdminServiceException) {
            return [];
        }

        return [
            'model' => $this->handbookValue,
        ];
    }

    public function name(): ?string
    {
        if ($this->handbookValue === null) {
            return null;
        }

        if (!$this->handbookValue->exists) {
            return __('Create') . ' ' . Str::singular($this->handbook->name);
        }

        return $this->handbookValue->getAttribute('name');
    }

    public function build(Repository $repository): View
    {
        // mimic Orchid's 404 page
        if ($this->handbook === null || $this->handbookValue === null) {
            return view('platform::errors.404');
        }

        $this->fillModelWithOldInput();

        return parent::build($repository);
    }

    /**
     * @return Layout[]|class-string[]
     */
    public function layout(): iterable
    {
        $rows = [
            MainRows::class,
        ];

        if ($this->handbookValue?->exists) {
            $rows[] = SystemLegend::class;
        }

        return $rows;
    }

    public function store(int $handbookId, StoreRequest $request): RedirectResponse
    {
        try {
            $model = $this->service->create($handbookId, $request);

            Toast::success('"' . $model->name . '" ' . __('has been created'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        return $this->redirectToIndexPage();
    }

    public function update(int $handbookId, int $handbookValueId, UpdateRequest $request): RedirectResponse
    {
        try {
            $model = $this->service->update($handbookId, $handbookValueId, $request);

            Toast::success('"' . $model->name . '" ' . __('has been updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        return redirect()->route(HandbookValueRouteName::EDIT, [
            'handbookId' => $handbookId,
            'handbookValueId' => $handbookValueId,
        ]);
    }

    public function delete(int $handbookId, int $handbookValueId): RedirectResponse
    {
        try {
            $model = $this->service->delete($handbookId, $handbookValueId);

            Toast::success('"' . $model->name . '" ' . __('has been deleted'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        return $this->redirectToIndexPage();
    }

    protected function redirectToIndexPage(): RedirectResponse
    {
        /** @var Route $currentRoute */
        $currentRoute = Request::route();

        return redirect()->route(HandbookValueRouteName::INDEX, [
            'handbookId' => (int) $currentRoute->parameter('handbookId'),
        ]);
    }

    public function commandBar(): array
    {
        if ($this->handbookValue?->exists) {
            return [
                Button::make(__('Delete'))
                    ->icon('bs.trash3')
                    ->class('btn icon-link rounded')
                    ->type(Color::DANGER)
                    ->route(HandbookValueRouteName::DELETE->value, [
                        'handbookId' => $this->handbook->id,
                        'handbookValueId' => $this->handbookValue->id,
                    ], false)
                    ->confirm(__('This action cannot be undone!')),

                Button::make(__('Save'))
                    ->icon('bs.save')
                    ->class('btn icon-link rounded')
                    ->type(Color::SUCCESS)
                    ->route(HandbookValueRouteName::UPDATE->value, [
                        'handbookId' => $this->handbook->id,
                        'handbookValueId' => $this->handbookValue->id,
                    ], false),
            ];
        }

        return [
            Button::make(__('Save'))
                ->icon('bs.save')
                ->class('btn icon-link rounded')
                ->type(Color::SUCCESS)
                ->route(HandbookValueRouteName::STORE->value, [
                    'handbookId' => $this->handbook->id,
                ], false),
        ];
    }

    /**
     * Keep user's input in case of an error.
     *
     * @return void
     *
     * @see \Illuminate\Session\Store::flashInput()
     * @see \Orchid\Platform\Http\Middleware\Turbo::handle()
     */
    private function fillModelWithOldInput(): void
    {
        $oldAttributes = Session::get('_old_input');
        if (!is_array($oldAttributes)) {
            return;
        }

        foreach ($oldAttributes as $attribute => $value) {
            if (!$this->handbookValue->hasAttribute($attribute)) {
                continue;
            }

            $this->handbookValue->setAttribute($attribute, $value);
        }
    }
}
