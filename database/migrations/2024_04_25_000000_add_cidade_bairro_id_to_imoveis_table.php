<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Schema::table('imoveis', function (Blueprint $table) {
            // Adicionar campos de relação para cidade e bairro
            $table->foreignId('cidade_id')->nullable()->after('bairro')
                  ->comment('Relação com a tabela cidades')
                  ->constrained('cidades')->nullOnDelete();
                  
            $table->foreignId('bairro_id')->nullable()->after('cidade_id')
                  ->comment('Relação com a tabela bairros')
                  ->constrained('bairros')->nullOnDelete();
                  
            // Adicionar índices para os novos campos
            $table->index('cidade_id');
            $table->index('bairro_id');
            $table->index(['cidade_id', 'bairro_id']);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('imoveis', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex(['cidade_id', 'bairro_id']);
            $table->dropIndex(['bairro_id']);
            $table->dropIndex(['cidade_id']);
            
            // Remover chaves estrangeiras e campos
            $table->dropForeign(['bairro_id']);
            $table->dropForeign(['cidade_id']);
            $table->dropColumn(['cidade_id', 'bairro_id']);
        });
    }
};
