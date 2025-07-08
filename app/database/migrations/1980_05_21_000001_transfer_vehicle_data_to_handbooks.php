<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const int HANDBOOK_TYPE_VEHICLE_CATEGORIES = 1;
    private const int HANDBOOK_TYPE_VEHICLE_TYPES = 2;
    private const int HANDBOOK_TYPE_VEHICLE_LINES = 3;

    public function up(): void
    {
        $this->transferCategoriesToHandbook();
        $this->transferTypesToHandbook();
        $this->transferLinesToHandbook();
    }

    public function down(): void
    {
        $this->restoreTypesFromHandbook();
        $this->restoreLinesFromHandbook();
        $this->restoreCategoriesFromHandbook();
    }


    private function transferCategoriesToHandbook(): void
    {
        $currentDate = Carbon::now()->toDateTimeString();

        DB::table('handbooks')->updateOrInsert([
            'type' => self::HANDBOOK_TYPE_VEHICLE_CATEGORIES,
        ], [
            'type' => self::HANDBOOK_TYPE_VEHICLE_CATEGORIES,
            'name' => 'Vehicle Categories',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ]);

        /** @var stdClass $categoriesHandbook */
        $categoriesHandbook = DB::table('handbooks')
            ->where('type', '=', self::HANDBOOK_TYPE_VEHICLE_CATEGORIES)
            ->first();

        /** @var array<int, int> $categoriesIdsMap */
        $categoriesIdsMap = [];

        foreach ($this->categoriesEnumMap() as $enum => $name) {
            $newId = DB::table('handbook_values')->insertGetId([
                'handbook_id' => (int) $categoriesHandbook->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ]);

            $categoriesIdsMap[$enum] = $newId;
        }

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->foreignId('category_id')
                ->nullable()
                ->references('id')
                ->on('handbook_values')
                ->cascadeOnDelete()
                ->nullOnDelete();
        });

        DB::table('vehicles')
            ->lazyById()
            ->each(static function (stdClass $vehicle) use ($categoriesIdsMap): void {
                DB::table('vehicles')
                    ->where('id', '=', (int) $vehicle->id)
                    ->update(['category_id' => $categoriesIdsMap[(int) $vehicle->category] ?? null]);
            });

        unset($categoriesIdsMap);

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->dropColumn('category');
        });
    }

    private function transferTypesToHandbook(): void
    {
        $currentDate = Carbon::now()->toDateTimeString();

        DB::table('handbooks')->updateOrInsert([
            'type' => self::HANDBOOK_TYPE_VEHICLE_TYPES,
        ], [
            'type' => self::HANDBOOK_TYPE_VEHICLE_TYPES,
            'name' => 'Vehicle Types',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ]);

        /** @var stdClass $typesHandbook */
        $typesHandbook = DB::table('handbooks')
            ->where('type', '=', self::HANDBOOK_TYPE_VEHICLE_TYPES)
            ->first();

        /** @var array<int, int> $typesIdsMap */
        $typesIdsMap = [];

        foreach ($this->typesEnumMap() as $enum => $name) {
            $newId = DB::table('handbook_values')->insertGetId([
                'handbook_id' => (int) $typesHandbook->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ]);

            $typesIdsMap[$enum] = $newId;
        }

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->foreignId('type_id')
                ->nullable()
                ->references('id')
                ->on('handbook_values')
                ->cascadeOnDelete()
                ->nullOnDelete();
        });

        DB::table('vehicles')
            ->lazyById()
            ->each(static function (stdClass $vehicle) use ($typesIdsMap): void {
                DB::table('vehicles')
                    ->where('id', '=', (int) $vehicle->id)
                    ->update(['type_id' => $typesIdsMap[(int) $vehicle->type] ?? null]);
            });

        unset($typesIdsMap);

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }

    private function transferLinesToHandbook(): void
    {
        $currentDate = Carbon::now()->toDateTimeString();

        DB::table('handbooks')->updateOrInsert([
            'type' => self::HANDBOOK_TYPE_VEHICLE_LINES,
        ], [
            'type' => self::HANDBOOK_TYPE_VEHICLE_LINES,
            'name' => 'Vehicle Lines',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ]);

        /** @var stdClass $linesHandbook */
        $linesHandbook = DB::table('handbooks')
            ->where('type', '=', self::HANDBOOK_TYPE_VEHICLE_LINES)
            ->first();

        /** @var array<int, int> $linesIdsMap */
        $linesIdsMap = [];

        DB::table('lines')
            ->lazyById()
            ->each(static function (stdClass $line) use ($currentDate, $linesHandbook, &$linesIdsMap): void {
                $newId = DB::table('handbook_values')->insertGetId([
                    'handbook_id' => (int) $linesHandbook->id,
                    'name' => (string) $line->name,
                    'slug' => (string) $line->slug,
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ]);

                $linesIdsMap[(int) $line->id] = $newId;
            });

        $this->disableAllTableTriggers();

        DB::table('vehicles')
            ->lazyById()
            ->each(static function (stdClass $vehicle) use ($linesIdsMap): void {
                DB::table('vehicles')
                    ->where('id', '=', (int) $vehicle->id)
                    ->update(['line_id' => $linesIdsMap[(int) $vehicle->line_id] ?? null]);
            });

        unset($linesIdsMap);

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->dropForeign(['line_id']);

            $table->foreign('line_id')
                ->references('id')
                ->on('handbook_values')
                ->cascadeOnDelete()
                ->nullOnDelete();
        });

        $this->enableAllTableTriggers();

        Schema::drop('lines');
    }

    private function restoreCategoriesFromHandbook(): void
    {
        /** @var stdClass $linesHandbook */
        $categoriesHandbook = DB::table('handbooks')
            ->where('type', '=', self::HANDBOOK_TYPE_VEHICLE_CATEGORIES)
            ->first();

        /** @var array<int, int> $categoriesIdsMap */
        $categoriesIdsMap = [];

        DB::table('handbook_values')
            ->where('handbook_id', '=', (int) $categoriesHandbook->id)
            ->lazyById()
            ->each(function (stdClass $handbookValue) use (&$categoriesIdsMap): void {
                $enum = array_find_key(
                    $this->categoriesEnumMap(),
                    static fn($name) => $name === (string) $handbookValue->name
                );

                if ($enum) {
                    $categoriesIdsMap[$handbookValue->id] = $enum;
                }
            });

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->smallInteger('category')->nullable()->index();
        });

        DB::table('vehicles')
            ->lazyById()
            ->each(static function (stdClass $vehicle) use ($categoriesIdsMap): void {
                DB::table('vehicles')
                    ->where('id', '=', (int) $vehicle->id)
                    ->update(['category' => $categoriesIdsMap[(int) $vehicle->category_id] ?? null]);
            });

        unset($categoriesIdsMap);

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        DB::table('handbook_values')
            ->where('handbook_id', '=', (int) $categoriesHandbook->id)
            ->delete();
    }

    private function restoreTypesFromHandbook(): void
    {
        /** @var stdClass $typesHandbook */
        $typesHandbook = DB::table('handbooks')
            ->where('type', '=', self::HANDBOOK_TYPE_VEHICLE_TYPES)
            ->first();

        /** @var array<int, int> $typesIdsMap */
        $typesIdsMap = [];

        DB::table('handbook_values')
            ->where('handbook_id', '=', (int) $typesHandbook->id)
            ->lazyById()
            ->each(function (stdClass $handbookValue) use (&$typesIdsMap): void {
                $enum = array_find_key(
                    $this->typesEnumMap(),
                    static fn($name) => $name === (string) $handbookValue->name
                );

                if ($enum) {
                    $typesIdsMap[$handbookValue->id] = $enum;
                }
            });

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->smallInteger('type')->nullable()->index();
        });

        DB::table('vehicles')
            ->lazyById()
            ->each(static function (stdClass $vehicle) use ($typesIdsMap): void {
                DB::table('vehicles')
                    ->where('id', '=', (int) $vehicle->id)
                    ->update(['type' => $typesIdsMap[(int) $vehicle->type_id] ?? null]);
            });

        unset($typesIdsMap);

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });

        DB::table('handbook_values')
            ->where('handbook_id', '=', (int) $typesHandbook->id)
            ->delete();
    }

    private function restoreLinesFromHandbook(): void
    {
        $currentDate = Carbon::now()->toDateTimeString();

        Schema::create('lines', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->smallInteger('status')->index();
            $table->smallInteger('sort')->default(500);
        });

        /** @var stdClass $linesHandbook */
        $linesHandbook = DB::table('handbooks')
            ->where('type', '=', self::HANDBOOK_TYPE_VEHICLE_LINES)
            ->first();

        /** @var array<int, int> $linesIdsMap */
        $linesIdsMap = [];

        DB::table('handbook_values')
            ->where('handbook_id', '=', (int) $linesHandbook->id)
            ->lazyById()
            ->each(static function (stdClass $handbookValue) use ($currentDate, &$linesIdsMap): void {
                $newId = DB::table('lines')->insertGetId([
                    'name' => (string) $handbookValue->name,
                    'slug' => (string) $handbookValue->slug,
                    'status' => 1, // mark as disabled (just in case)
                    'sort' => 500,
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ]);

                $linesIdsMap[$handbookValue->id] = $newId;
            });

        $this->disableAllTableTriggers();

        DB::table('vehicles')
            ->lazyById()
            ->each(static function (stdClass $vehicle) use ($linesIdsMap): void {
                DB::table('vehicles')
                    ->where('id', '=', (int) $vehicle->id)
                    ->update(['line_id' => $linesIdsMap[(int) $vehicle->line_id] ?? null]);
            });

        unset($linesIdsMap);

        Schema::table('vehicles', static function (Blueprint $table): void {
            $table->dropForeign(['line_id']);

            $table->foreign('line_id')
                ->references('id')
                ->on('lines')
                ->cascadeOnDelete()
                ->nullOnDelete();
        });

        $this->enableAllTableTriggers();

        DB::table('handbook_values')
            ->where('handbook_id', '=', (int) $linesHandbook->id)
            ->delete();
    }

    /**
     * @return array<int, string>
     */
    private function categoriesEnumMap(): array
    {
        return [
            1 => 'Air',
            2 => 'Aquatic',
            3 => 'Ground',
            4 => 'Repulsorlift',
            5 => 'Space Station',
            6 => 'Starship',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function typesEnumMap(): array
    {
        return [
            1 => 'Starfighter',
            2 => 'Bomber',
            3 => 'Gunship',

            4 => 'Corvette',
            5 => 'Frigate',
            6 => 'Cruiser',
            7 => 'Heavy cruiser',
            8 => 'Destroyer',
            9 => 'Battlecruiser',
            10 => 'Dreadnought',

            11 => 'Airspeeder',
            12 => 'Landspeeder',
            13 => 'Walker',
            14 => 'Shuttle',
            15 => 'Tank',
            16 => 'Transport',
            17 => 'Freighter',

            18 => 'Battle station',
            19 => 'Podracer',
            20 => 'Atmospheric fighter',
            21 => 'Train',
            22 => 'Tug',
            23 => 'Yacht',

            32767 => 'Other',
        ];
    }

    private function disableAllTableTriggers(): void
    {
        DB::statement('alter table vehicles disable trigger all');
    }

    private function enableAllTableTriggers(): void
    {
        DB::statement('alter table vehicles enable trigger all');
    }
};
