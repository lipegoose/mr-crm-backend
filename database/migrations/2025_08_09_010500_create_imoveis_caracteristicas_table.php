<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis_caracteristicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->foreignId('caracteristica_id')->constrained('caracteristicas')->cascadeOnDelete();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Restrições e índices
            $table->unique(['imovel_id', 'caracteristica_id'], 'imov_carac_unique');
            $table->index('caracteristica_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imoveis_caracteristicas');
    }
};
