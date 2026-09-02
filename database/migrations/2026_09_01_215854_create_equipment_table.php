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
