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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('equipment_models')->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');

            // Datos específicos del cliente
            $table->date('entry_date')->nullable();          // fecha de ingreso
            $table->string('warranty_status')->nullable();   // en garantía / sin garantía / leasing

            // Identificación
            $table->string('risk_class')->nullable();        // clasificación INVIMA (I/IIA/IIB/III)
            $table->json('specialties')->nullable();         // clasificación por especialidad (multi)
            $table->string('invima_registry')->nullable();   // registro INVIMA
            $table->string('manufacturer')->nullable();      // fabricante
            $table->string('origin_country')->nullable();    // país de origen
            $table->string('maintenance_frequency')->nullable(); // periodicidad
            $table->string('acquisition_type')->nullable();  // compra/comodato/leasing/donación

            // Características técnicas
            $table->string('voltage')->nullable();
            $table->string('amperage')->nullable();
            $table->string('current')->nullable();
            $table->string('power')->nullable();
            $table->string('temperature')->nullable();
            $table->string('pressure')->nullable();
            $table->string('weight')->nullable();
            $table->string('speed')->nullable();
            $table->string('predominant_technology')->nullable();
            $table->text('technical_observations')->nullable();
            $table->text('general_observations')->nullable();

            // Plantilla de mantenimiento y accesorios (se ejecutan/marcan en cada OT)
            $table->json('maintenance_tasks')->nullable();   // subtareas que aplican al equipo
            $table->json('accessories')->nullable();         // accesorios que aplican al equipo
            $table->text('components')->nullable();          // componentes/accesorios (texto)
            $table->text('default_ot_observations')->nullable(); // obs. por defecto para OT

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
