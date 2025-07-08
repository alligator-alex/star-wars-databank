<?php

declare(strict_types=1);

namespace App\Modules\Core\Public\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;

class Breadcrumbs
{
    protected static self $instance;

    private Factory $viewFactory;

    /** @var array<string, array<string, string>> */
    private array $items = [];

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            static::$instance = new self();
        }

        return self::$instance;
    }

    public static function add(string $title, string $routeName, mixed $routeParams = []): void
    {
        static::getInstance()->addItem($title, $routeName, $routeParams);
    }

    private function addItem(string $title, string $routeName, mixed $routeParams = []): void
    {
        $key = mb_strtolower($title) . '-' . $routeName;

        if (!isset($this->items[$key])) {
            $this->items[$key] = [
                'title' => $title,
                'route' => route($routeName, $routeParams),
            ];
        }
    }

    public static function render(): View
    {
        return static::getInstance()->viewFactory->make('public.common.breadcrumbs', [
            'items' => array_values(static::getInstance()->items),
        ]);
    }

    private function __construct()
    {
        $this->viewFactory = app()->make(Factory::class);
    }

    private function __clone(): void
    {
    }
}
