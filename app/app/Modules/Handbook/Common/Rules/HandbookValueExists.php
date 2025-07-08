<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Rules;

use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;

class HandbookValueExists implements ValidationRule
{
    private HandbookType $handbookType;

    private string $field;

    public function __construct(HandbookType $handbookType, string $field = 'id')
    {
        $this->handbookType = $handbookType;
        $this->field = $field;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var HandbookRepository $handbookRepository */
        $handbookRepository = app()->make(HandbookRepository::class);

        /** @var HandbookValueRepository $handbookValueRepository */
        $handbookValueRepository = app()->make(HandbookValueRepository::class);

        /** @var Handbook|null $handbook */
        $handbook = $handbookRepository->queryBuilder()
            ->where('type', '=', $this->handbookType->value)
            ->first();

        if ($handbook === null) {
            $fail('Handbook ":handbook" does not exist')->translate([
                'handbook' => $this->handbookType->name,
            ]);

            return;
        }

        /** @var Builder<HandbookValue> $handbookValue */
        $handbookValue = $handbookValueRepository->queryBuilder()
            ->where('handbook_id', '=', $handbook->id)
            ->where($this->field, '=', $value);

        if (!$handbookValue->exists()) {
            $fail('Value ":value" of handbook ":handbook" does not exist')->translate([
                'value' => $value,
                'handbook' => $this->handbookType->name,
            ]);
        }
    }
}
