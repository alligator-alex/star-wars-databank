<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const int ENTITY_TYPE_VEHICLE = 1;

    public function up(): void
    {
        $this->transferFactions();
        $this->transferManufacturers();
        $this->transferAppearances();
    }

    public function down(): void
    {
        $this->restoreFactions();
        $this->restoreManufacturers();
        $this->restoreAppearances();
    }

    private function transferFactions(): void
    {
        Schema::create('factionables', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faction_id')
                ->constrained('factions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->bigInteger('factionable_id');
            $table->smallInteger('factionable_type');
            $table->boolean('main')->default(false);
        });

        DB::table('vehicle_faction')->lazyById()->each(static function (stdClass $model): void {
            DB::table('factionables')->insert([
                'faction_id' => (int) $model->faction_id,
                'factionable_id' => (int) $model->vehicle_id,
                'factionable_type' => self::ENTITY_TYPE_VEHICLE,
                'main' => (bool) $model->main,
            ]);
        });

        Schema::dropIfExists('vehicle_faction');
    }

    private function transferManufacturers(): void
    {
        Schema::create('manufacturables', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manufacturer_id')
                ->constrained('manufacturers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->bigInteger('manufacturable_id');
            $table->smallInteger('manufacturable_type');
        });

        DB::table('vehicle_manufacturer')->lazyById()->each(static function (stdClass $model): void {
            DB::table('manufacturables')->insert([
                'manufacturer_id' => (int) $model->manufacturer_id,
                'manufacturable_id' => (int) $model->vehicle_id,
                'manufacturable_type' => self::ENTITY_TYPE_VEHICLE,
            ]);
        });

        Schema::dropIfExists('vehicle_manufacturer');
    }

    private function transferAppearances(): void
    {
        Schema::create('mediables', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')
                ->constrained('media')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->bigInteger('mediable_id');
            $table->smallInteger('mediable_type');
        });

        DB::table('vehicle_appearance')->lazyById()->each(static function (stdClass $model): void {
            DB::table('mediables')->insert([
                'media_id' => (int) $model->media_id,
                'mediable_id' => (int) $model->vehicle_id,
                'mediable_type' => self::ENTITY_TYPE_VEHICLE,
            ]);
        });

        Schema::dropIfExists('vehicle_appearance');
    }

    private function restoreFactions(): void
    {
        Schema::create('vehicle_faction', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('faction_id')
                ->constrained('factions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->boolean('main')->default(false);

            $table->unique(['vehicle_id', 'faction_id']);
        });

        DB::table('factionables')
            ->where('factionable_type', '=', self::ENTITY_TYPE_VEHICLE)
            ->lazyById()
            ->each(static function (stdClass $model): void {
                DB::table('vehicle_faction')->insert([
                    'vehicle_id' => (int) $model->factionable_id,
                    'faction_id' => (int) $model->faction_id,
                    'main' => (bool) $model->main,
                ]);
            });

        Schema::dropIfExists('factionables');
    }

    private function restoreManufacturers(): void
    {
        Schema::create('vehicle_manufacturer', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('manufacturer_id')
                ->constrained('manufacturers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(['vehicle_id', 'manufacturer_id']);
        });

        DB::table('manufacturables')
            ->where('manufacturable_type', '=', self::ENTITY_TYPE_VEHICLE)
            ->lazyById()
            ->each(static function (stdClass $model): void {
                DB::table('vehicle_manufacturer')->insert([
                    'vehicle_id' => (int) $model->manufacturable_id,
                    'manufacturer_id' => (int) $model->manufacturer_id,
                ]);
            });

        Schema::dropIfExists('manufacturables');
    }

    private function restoreAppearances(): void
    {
        Schema::create('vehicle_appearance', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('media_id')
                ->constrained('media')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(['vehicle_id', 'media_id']);
        });

        DB::table('mediables')
            ->where('mediable_type', '=', self::ENTITY_TYPE_VEHICLE)
            ->lazyById()
            ->each(static function (stdClass $model): void {
                DB::table('vehicle_appearance')->insert([
                    'vehicle_id' => (int) $model->mediable_id,
                    'media_id' => (int) $model->media_id,
                ]);
            });

        Schema::dropIfExists('mediables');
    }
};
