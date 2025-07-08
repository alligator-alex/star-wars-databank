<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handbooks', static function (Blueprint $table): void {
            $table->id();
            $table->smallInteger('type');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('handbook_values', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('handbook_id')
                ->constrained('handbooks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('handbook_values');
        Schema::drop('handbooks');
    }
};
