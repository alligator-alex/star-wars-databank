<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Controllers;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Core\Common\Components\Model;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;

/**
 * Basic admin edit screen controller.
 *
 * @template TModel of Model
 */
abstract class BaseEditScreen extends Screen
{
    /** @var TModel|null */
    protected ?Model $model = null;

    abstract protected function listRoute(): BackedEnum;
    abstract protected function createRoute(): BackedEnum;
    abstract protected function updateRoute(): BackedEnum;
    abstract protected function deleteRoute(): BackedEnum;

    /**
     * @param int|null $id
     *
     * @return iterable<string, mixed>
     */
    abstract public function query(?int $id = null): iterable;

    /**
     * @inheritDoc
     */
    public function build(Repository $repository): View
    {
        // mimic Orchid's 404 page
        if (is_null($this->model)) {
            return view('platform::errors.404');
        }

        $this->fillModelWithOldInput();

        return parent::build($repository);
    }

    public function name(): ?string
    {
        if (is_null($this->model)) {
            return __('Not found');
        }

        if (!$this->model->exists) {
            $parts = explode('\\', $this->model::class);
            return __('Create new') . ' ' . __(end($parts));
        }

        return $this->model->getAttribute('name');
    }

    public function commandBar(): array
    {
        $buttons = [];

        if ($this->model?->exists) {
            $buttons[] = Button::make(__('Delete'))
                ->icon('bs.trash3')
                ->class('btn icon-link rounded')
                ->type(Color::DANGER)
                ->route($this->deleteRoute()->value, (int) $this->model->getAttribute('id'), false)
                ->confirm(__('This action cannot be undone!'));
        }

        $saveButton = Button::make(__('Save'))
            ->icon('bs.save')
            ->class('btn icon-link rounded')
            ->type(Color::SUCCESS);

        if ($this->model?->exists) {
            $saveButton->route($this->updateRoute()->value, (int) $this->model->getAttribute('id'), false);
        } else {
            $saveButton->route(name: $this->createRoute()->value, absolute: false);
        }

        $buttons[] = $saveButton;

        return $buttons;
    }

    protected function refreshPage(): RedirectResponse
    {
        /** @var Route $currentRoute */
        $currentRoute = Request::route();

        return redirect()->route($currentRoute->getName(), (int) $currentRoute->parameter('id'));
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return RedirectResponse
     */
    protected function redirectToListPage(array $parameters = []): RedirectResponse
    {
        return redirect()->route($this->listRoute(), $parameters);
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
            if (!$this->model->hasAttribute($attribute)) {
                continue;
            }

            $this->model->setAttribute($attribute, $value);
        }
    }

    /**
     * Handle and exception.
     *
     * - shows an Alert with error message
     * - stores user's input to session (if any)
     * - refreshes the page
     *
     * @param AdminServiceException $exception
     * @param AdminFormRequest|null $request
     *
     * @return RedirectResponse
     */
    protected function handleException(
        AdminServiceException $exception,
        ?AdminFormRequest $request = null
    ): RedirectResponse {
        Alert::error($exception->getMessage());

        if ($request) {
            Session::flashInput($request->toArray());
        }

        return redirect()->refresh();
    }
}
