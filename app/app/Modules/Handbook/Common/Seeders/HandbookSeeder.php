<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Seeders;

use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use Illuminate\Database\Seeder;
use App\Modules\Handbook\Common\Enums\HandbookType;

class HandbookSeeder extends Seeder
{
    public function __construct(private readonly HandbookRepository $repository)
    {
    }

    public function run(): void
    {
        $handbooks = [
            [
                'type' => HandbookType::VEHICLE_LINE,
                'name' => 'Vehicle Lines',
            ],
            [
                'type' => HandbookType::VEHICLE_CATEGORY,
                'name' => 'Vehicle Categories',
            ],
            [
                'type' => HandbookType::VEHICLE_TYPE,
                'name' => 'Vehicle Types',
            ],
            [
                'type' => HandbookType::DROID_LINE,
                'name' => 'Droid Lines',
            ],
            [
                'type' => HandbookType::DROID_MODEL,
                'name' => 'Droid Models',
            ],
            [
                'type' => HandbookType::DROID_CLASS,
                'name' => 'Droid Classes',
            ],
        ];

        $keepTypes = [];
        foreach ($handbooks as $handbookData) {
            $handbook = $this->repository->findOneByType($handbookData['type']);
            if (!$handbook) {
                $handbook = $this->repository->newModel();

                $handbook->type = $handbookData['type']->value;
            }

            $handbook->name = $handbookData['name'];

            $this->repository->save($handbook);

            $keepTypes[] = $handbookData['type']->value;
        }

        $this->repository->queryBuilder()
            ->whereNotIn('type', $keepTypes)
            ->delete();
    }
}
