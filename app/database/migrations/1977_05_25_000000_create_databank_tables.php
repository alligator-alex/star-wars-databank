<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lines', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->smallInteger('status')->index();
            $table->smallInteger('sort')->default(500);
        });

        Schema::create('factions', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->smallInteger('status')->index();
            $table->smallInteger('sort')->default(500);
        });

        Schema::create('manufacturers', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->smallInteger('status')->index();
            $table->smallInteger('sort')->default(500);
        });

        Schema::create('media', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->smallInteger('status')->index();
            $table->integer('sort')->default(500);
            $table->smallInteger('type')->nullable()->index();
            $table->date('release_date')->nullable();
            $table->foreignId('poster_id')
                ->nullable()
                ->constrained('attachments')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('vehicles', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->smallInteger('status')->index();
            $table->integer('sort')->default(500);
            $table->string('external_url')->nullable();
            $table->boolean('canon')->default(false);
            $table->smallInteger('category')->nullable()->index();
            $table->smallInteger('type')->nullable()->index();
            $table->foreignId('line_id')
                ->nullable()
                ->index()
                ->constrained('lines')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('image_id')
                ->nullable()
                ->constrained('attachments')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->text('description')->nullable();
            $table->jsonb('technical_specifications')->nullable();
            $table->jsonb('page_settings')->nullable();
        });

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_appearance');
        Schema::dropIfExists('vehicle_manufacturer');
        Schema::dropIfExists('vehicle_faction');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('media');
        Schema::dropIfExists('manufacturers');
        Schema::dropIfExists('factions');
        Schema::dropIfExists('lines');
    }
};
