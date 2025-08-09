<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominios_caracteristicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominio_id')->constrained('condominios')->cascadeOnDelete();
            $table->foreignId('caracteristica_id')->constrained('caracteristicas')->cascadeOnDelete();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Restrições e índices
            $table->unique(['condominio_id', 'caracteristica_id'], 'cond_carac_unique');
            $table->index('caracteristica_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condominios_caracteristicas');
    }
};
