<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Repositories;

use App\Modules\Core\Common\Repositories\ModelRepository;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends ModelRepository<HandbookValue>
 */
class HandbookValueRepository extends ModelRepository
{
    protected string $modelClass = HandbookValue::class;

    public function findOneBySlug(int $handbookId, string $slug): ?HandbookValue
    {
        $query = $this->queryBuilder()
            ->where('handbook_id', '=', $handbookId)
            ->where('slug', '=', $slug);

        /** @var HandbookValue|null $model */
        $model = $query->first();

        return $model;
    }

    public function count(HandbookType $type): int
    {
        return $this->queryBuilder()
            ->whereHas('handbook', static fn (Builder $subQuery): Builder => $subQuery->where('type', '=', $type->value))
            ->count();
    }

    /**
     * @param HandbookType $type
     * @param string $columnAsKey
     *
     * @return array<string, string>
     */
    public function dropdownList(HandbookType $type, string $columnAsKey = 'id'): array
    {
        static $listCache = [];

        if (!isset($listCache[$type->value])) {
            $listCache[$type->value] = [];

            $query = $this->queryBuilder()
                ->whereHas('handbook', static fn (Builder $subQuery) => $subQuery->where('type', '=', $type->value))
                ->orderBy('name')
                ->orderByDesc('id');

            /** @var HandbookValue $item */
            foreach ($query->get() as $item) {
                $listCache[$type->value][$item->{$columnAsKey}] = $item->name;
            }

            // move "Other" to the end
            if ($type === HandbookType::VEHICLE_TYPE) {
                $otherKey = array_search('Other', $listCache[$type->value], true);

                if ($otherKey) {
                    $otherItem = $listCache[$type->value][$otherKey];
                    unset($listCache[$type->value][$otherKey]);
                    $listCache[$type->value][$otherKey] = $otherItem;
                }
            }
        }

        return $listCache[$type->value];
    }
}
