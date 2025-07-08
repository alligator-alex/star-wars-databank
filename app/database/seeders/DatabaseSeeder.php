<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Handbook\Common\Seeders\HandbookSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            HandbookSeeder::class,
        ]);
    }
}
