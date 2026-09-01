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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // nombre de la empresa
            $table->string('nit')->unique();        // identificación
            $table->string('email');                // correo (= login de la cuenta)
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('phone')->nullable();    // celular
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
