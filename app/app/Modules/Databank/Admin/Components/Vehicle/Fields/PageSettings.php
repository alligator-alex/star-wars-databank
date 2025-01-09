<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Fields;

use App\Modules\Databank\Common\Models\Vehicle;
use Orchid\Screen\Field;

abstract class PageSettings extends Field
{
    protected Vehicle $vehicle;

    protected function translateAttributes(): static
    {
        $lang = $this->get('lang');

        collect($this->attributes)
            ->intersectByKeys(array_flip($this->translations))
            ->each(function ($value, $key) use ($lang) {
                $translation = __($value, [], $lang);
                $this->set($key, is_string($translation) ? $translation : $value);
            });

        return $this;
    }

    protected function markFieldWithError(): static
    {
        if (! $this->hasError()) {
            return $this;
        }

        $class = $this->get('class');

        return $this->set('class', $class.' is-invalid');
    }

    protected function hasError(): bool
    {
        return optional(session('errors'))?->has($this->getOldName()) ?? false;
    }
}
