<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis_detalhes', function (Blueprint $table) {
            // PK = FK para imoveis.id (1:1)
            $table->foreignId('id')->primary()->constrained('imoveis')->cascadeOnDelete();

            // Descrição e conteúdo
            $table->string('titulo_anuncio', 255)->nullable();
            $table->boolean('mostrar_titulo')->default(true);
            $table->text('descricao')->nullable();
            $table->boolean('mostrar_descricao')->default(true);
            $table->text('palavras_chave')->nullable()->comment('Palavras-chave para SEO, separadas por vírgula');
            $table->text('observacoes_internas')->nullable()->comment('Observações visíveis apenas internamente');
            $table->string('tour_virtual_url', 255)->nullable();

            // Documentação
            $table->string('matricula', 100)->nullable();
            $table->string('inscricao_municipal', 100)->nullable();
            $table->string('inscricao_estadual', 100)->nullable();

            // Comissão e exclusividade
            $table->decimal('valor_comissao', 10, 2)->nullable();
            $table->enum('tipo_comissao', ['PORCENTAGEM', 'VALOR'])->nullable();
            $table->boolean('exclusividade')->default(false);
            $table->date('data_inicio_exclusividade')->nullable();
            $table->date('data_fim_exclusividade')->nullable();
            $table->text('observacoes_privadas')->nullable();

            // Campos JSON
            $table->json('config_exibicao')->nullable()->comment('Configurações específicas de exibição');
            $table->json('dados_permuta')->nullable()->comment('Detalhes sobre permuta (tipo aceito, valor máximo)');
            $table->json('seo')->nullable()->comment('Metadados adicionais para SEO');

            // Auditoria
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imoveis_detalhes');
    }
};
