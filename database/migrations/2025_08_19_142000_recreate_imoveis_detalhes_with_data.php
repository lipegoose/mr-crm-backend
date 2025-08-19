<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro, desabilitar verificação de chaves estrangeiras
        Schema::disableForeignKeyConstraints();
        
        // Segundo, excluir a tabela existente
        Schema::dropIfExists('imoveis_detalhes');
        
        // Terceiro, recriar a tabela com a estrutura correta incluindo imovel_id
        Schema::create('imoveis_detalhes', function (Blueprint $table) {
            // PK = FK para imoveis.id (1:1)
            $table->id();
            
            // Nova coluna imovel_id como FK para imoveis.id
            $table->unsignedBigInteger('imovel_id')->index();

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
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
        
        // Quarto, inserir registros na nova tabela imoveis_detalhes com base nos registros da tabela imoveis
        /* $imoveis = DB::table('imoveis')->get();
        
        foreach ($imoveis as $imovel) {
            DB::table('imoveis_detalhes')->insert([
                'imovel_id' => $imovel->id,
                'created_at' => $imovel->created_at,
                'updated_at' => $imovel->updated_at,
                'created_by' => $imovel->created_by ?? null,
                'updated_by' => $imovel->updated_by ?? null
            ]);
        } */
        
        // Reabilitar verificação de chaves estrangeiras
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Desabilitar verificação de chaves estrangeiras
        Schema::disableForeignKeyConstraints();
        
        // Excluir a tabela
        Schema::dropIfExists('imoveis_detalhes');
        
        // Recriar a tabela sem o campo imovel_id
        Schema::create('imoveis_detalhes', function (Blueprint $table) {
            // PK = FK para imoveis.id (1:1)
            $table->id();

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
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
        
        // Reabilitar verificação de chaves estrangeiras
        Schema::enableForeignKeyConstraints();
    }
};
