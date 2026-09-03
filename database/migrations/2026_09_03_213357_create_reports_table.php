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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->json('filters')->nullable();
            $table->string('status')->default('pending'); // pending | processing | done | failed
            $table->foreignId('generated_by')->constrained('users');
            $table->string('file_path')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->foreignId('downloaded_by')->nullable()->constrained('users');
            $table->timestamp('downloaded_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
