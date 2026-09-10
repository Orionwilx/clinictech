<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fotos/evidencias subidas por el técnico durante el diligenciamiento de la OT.
     * Se guardan comprimidas (JPEG, lado máximo acotado) en el disco public.
     */
    public function up(): void
    {
        Schema::create('work_order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('path');                    // ruta en el disco public
            $table->string('original_name')->nullable();
            $table->unsignedInteger('size')->nullable(); // bytes tras la compresión
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_photos');
    }
};
