<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis_precos_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->enum('tipo_negocio', ['VENDA', 'ALUGUEL', 'TEMPORADA'])->comment('Tipo do negócio: Venda, Aluguel, Temporada');
            $table->decimal('valor', 12, 2)->comment('Valor do imóvel para o período');
            $table->date('data_inicio')->comment('Data de início da vigência do preço');
            $table->date('data_fim')->nullable()->comment('Data de fim da vigência do preço');
            $table->string('motivo', 255)->nullable()->comment('Motivo da alteração de preço');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Índices
            $table->index('imovel_id');
            $table->index(['imovel_id', 'tipo_negocio']);
            $table->index(['imovel_id', 'data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imoveis_precos_historico');
    }
};
