<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogos de opciones configurables por admin (fuente de los checkboxes
     * en categorías y equipos). La selección se guarda como JSON de nombres
     * (snapshot), por lo que estas tablas no llevan pivotes.
     */
    public function up(): void
    {
        foreach (['maintenance_tasks', 'accessories', 'specialties'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('specialties');
        Schema::dropIfExists('accessories');
        Schema::dropIfExists('maintenance_tasks');
    }
};
