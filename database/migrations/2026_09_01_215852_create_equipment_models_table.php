<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            // Los defaults del equipo viven en la categoría, no en el modelo
            $table->foreignId('category_id')->constrained('equipment_categories')->restrictOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['brand_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_models');
    }
};
