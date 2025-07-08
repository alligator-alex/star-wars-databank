<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers;

use App\Modules\Core\Common\Components\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

/**
 * Basic admin edit screen controller.
 *
 * @template TModel of Model
 */
abstract class BaseDetailController extends Screen
{
    /** @var TModel|null */
    protected ?Model $model = null;

    /**
     * @param int|null $id
     *
     * @return iterable<string, mixed>
     */
    abstract public function query(?int $id = null): iterable;

    /**
     * @return class-string
     */
    private function resolveRouteEnum(): string
    {
        static $routeEnum = null;
        if ($routeEnum === null) {
            $classBaseName = class_basename($this->model);
            $routeEnum = '\\App\\Modules\\' . $classBaseName . '\\Admin\\Enums\\' . $classBaseName . 'RouteName';
        }

        return $routeEnum;
    }

    /**
     * @inheritDoc
     */
    public function build(Repository $repository): View
    {
        // mimic Orchid's 404 page
        if ($this->model === null) {
            return view('platform::errors.404');
        }

        $this->fillModelWithOldInput();

        return parent::build($repository);
    }

    public function name(): ?string
    {
        if ($this->model === null) {
            return null;
        }

        if (!$this->model->exists) {
            return __('Create') . ' ' . __(class_basename($this->model));
        }

        return $this->model->getAttribute('name');
    }

    public function commandBar(): array
    {
        if ($this->model === null) {
            return [];
        }

        if ($this->model->exists) {
            return [
                Button::make(__('Delete'))
                    ->icon('bs.trash3')
                    ->class('btn icon-link rounded')
                    ->type(Color::DANGER)
                    ->route($this->resolveRouteEnum()::DELETE->value, $this->model->getKey(), false)
                    ->confirm(__('This action cannot be undone!')),

                Button::make(__('Save'))
                    ->icon('bs.save')
                    ->class('btn icon-link rounded')
                    ->type(Color::SUCCESS)
                    ->route($this->resolveRouteEnum()::UPDATE->value, $this->model->getKey(), false)
            ];
        }

        return [
            Button::make(__('Save'))
                ->icon('bs.save')
                ->class('btn icon-link rounded')
                ->type(Color::SUCCESS)
                ->route(name: $this->resolveRouteEnum()::STORE->value, absolute: false)
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
            if (!$this->model->hasAttribute($attribute)) {
                continue;
            }

            $this->model->setAttribute($attribute, $value);
        }
    }
}
