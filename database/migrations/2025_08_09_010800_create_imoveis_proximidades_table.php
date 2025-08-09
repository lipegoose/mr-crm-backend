<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis_proximidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->foreignId('proximidade_id')->constrained('proximidades')->cascadeOnDelete();
            $table->string('distancia_texto', 50)->nullable()->comment('Exemplo: 500m, 2km');
            $table->unsignedInteger('distancia_metros')->nullable()->comment('Distância em metros para ordenação');
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Restrições e índices
            $table->unique(['imovel_id', 'proximidade_id'], 'imov_prox_unique');
            $table->index('proximidade_id');
            $table->index('distancia_metros');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imoveis_proximidades');
    }
};
