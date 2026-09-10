<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categoría = plantilla completa del equipo. Sus valores se copian
     * (snapshot) al equipo al crearlo; cambios posteriores no afectan
     * equipos existentes.
     */
    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();

            // Plantilla — Identificación (fabricante/país viven en la marca)
            $table->string('risk_class')->nullable();            // INVIMA (I/IIA/IIB/III)
            $table->json('specialties')->nullable();             // nombres del catálogo specialties

            // Plantilla — Características técnicas
            $table->string('voltage')->nullable();
            $table->string('amperage')->nullable();
            $table->string('current')->nullable();
            $table->string('power')->nullable();
            $table->string('temperature')->nullable();
            $table->string('pressure')->nullable();
            $table->string('weight')->nullable();
            $table->string('speed')->nullable();
            $table->string('predominant_technology')->nullable();

            // Plantilla — Mantenimiento / accesorios
            $table->json('maintenance_tasks')->nullable();       // nombres del catálogo maintenance_tasks
            $table->json('accessories')->nullable();             // nombres del catálogo accessories
            $table->text('components')->nullable();
            $table->text('default_ot_observations')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_categories');
    }
};
