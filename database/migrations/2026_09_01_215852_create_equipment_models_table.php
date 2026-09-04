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
            $table->string('name');
            // Defaults técnicos que se auto-completan al seleccionar el modelo en un equipo
            $table->string('type')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('origin_country')->nullable();
            $table->string('risk_class')->nullable();
            $table->json('specialties')->nullable();
            $table->string('invima_registry')->nullable();
            $table->string('maintenance_frequency')->nullable();
            $table->json('maintenance_tasks')->nullable();
            $table->json('accessories')->nullable();
            $table->timestamps();
            $table->unique(['brand_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_models');
    }
};
