<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('droids', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->smallInteger('status')->index();
            $table->integer('sort')->default(500);
            $table->string('external_url')->nullable();
            $table->boolean('canon')->default(false);
            $table->foreignId('line_id')
                ->nullable()
                ->index()
                ->constrained('handbook_values')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('model_id')
                ->nullable()
                ->index()
                ->constrained('handbook_values')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('class_id')
                ->nullable()
                ->index()
                ->constrained('handbook_values')
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
    }

    public function down(): void
    {
        Schema::dropIfExists('droids');
    }
};
