<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis', function (Blueprint $table) {
            $table->id();

            // Código de referência - único, gerado automaticamente mas editável
            $table->string('codigo_referencia', 50)->unique()->comment('Código único para referência do imóvel');

            // Proprietário e relacionamentos
            $table->foreignId('proprietario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('corretor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('condominio_id')->nullable()->constrained('condominios')->nullOnDelete();

            // Tipo e classificação do imóvel
            $table->string('tipo', 50)->comment('Tipo principal: Apartamento, Casa, Comercial, Terreno, etc.');
            $table->string('subtipo', 50)->nullable()->comment('Subtipo: Cobertura, Duplex, Em Condomínio, etc.');
            $table->string('perfil', 50)->comment('Perfil: Residencial, Comercial, Rural, etc.');
            $table->string('situacao', 50)->comment('Situação: Pronto, Em construção, Na planta, etc.');

            // Informações adicionais
            $table->integer('ano_construcao')->nullable();
            $table->string('incorporacao', 100)->nullable();
            $table->string('posicao_solar', 50)->nullable();
            $table->enum('terreno', ['PLANO', 'ACLIVE', 'DECLIVE'])->nullable();
            $table->boolean('escriturado')->default(false);
            $table->boolean('esquina')->default(false);
            $table->boolean('mobiliado')->default(false);
            $table->boolean('averbado')->default(false);

            // Cômodos (quantidades)
            $table->unsignedTinyInteger('dormitorios')->default(0);
            $table->unsignedTinyInteger('suites')->default(0);
            $table->unsignedTinyInteger('banheiros')->default(0);
            $table->unsignedTinyInteger('garagens')->default(0);
            $table->boolean('garagem_coberta')->default(false);
            $table->boolean('box_garagem')->default(false);
            $table->unsignedTinyInteger('sala_tv')->default(0);
            $table->unsignedTinyInteger('sala_jantar')->default(0);
            $table->unsignedTinyInteger('sala_estar')->default(0);
            $table->unsignedTinyInteger('lavabo')->default(0);
            $table->unsignedTinyInteger('area_servico')->default(0);
            $table->unsignedTinyInteger('cozinha')->default(0);
            $table->unsignedTinyInteger('closet')->default(0);
            $table->unsignedTinyInteger('escritorio')->default(0);
            $table->unsignedTinyInteger('dependencia_servico')->default(0);
            $table->unsignedTinyInteger('copa')->default(0);

            // Medidas
            $table->decimal('area_construida', 10, 2)->nullable();
            $table->decimal('area_privativa', 10, 2)->nullable();
            $table->decimal('area_total', 10, 2)->nullable();
            $table->enum('unidade_medida', ['m²', 'ha'])->default('m²');

            // Negócio e preços
            $table->enum('tipo_negocio', ['VENDA', 'ALUGUEL', 'VENDA_ALUGUEL', 'TEMPORADA'])->comment('Tipo de negócio: Venda, Aluguel, etc.');
            $table->decimal('preco_venda', 12, 2)->nullable();
            $table->decimal('preco_aluguel', 12, 2)->nullable();
            $table->decimal('preco_temporada', 12, 2)->nullable();
            $table->boolean('mostrar_preco')->default(true);
            $table->string('preco_alternativo', 100)->nullable()->comment('Texto quando não mostra preço (ex: Consulte)');
            $table->decimal('preco_anterior', 12, 2)->nullable();
            $table->boolean('mostrar_preco_anterior')->default(false);
            $table->decimal('preco_iptu', 10, 2)->nullable();
            $table->enum('periodo_iptu', ['ANUAL', 'MENSAL'])->default('ANUAL');
            $table->decimal('preco_condominio', 10, 2)->nullable();
            $table->boolean('financiado')->default(false);
            $table->boolean('aceita_financiamento')->default(false);
            $table->boolean('minha_casa_minha_vida')->default(false);
            $table->decimal('total_taxas', 10, 2)->nullable();
            $table->text('descricao_taxas')->nullable();
            $table->boolean('aceita_permuta')->default(false);

            // Localização
            $table->string('cep', 8)->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('logradouro', 255)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->boolean('mostrar_endereco')->default(true);
            $table->boolean('mostrar_numero')->default(true);
            $table->boolean('mostrar_proximidades')->default(false);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Status e publicação
            $table->enum('status', ['ATIVO', 'INATIVO', 'VENDIDO', 'ALUGADO', 'RESERVADO', 'EM_NEGOCIACAO'])->default('ATIVO');
            $table->boolean('publicar_site')->default(true);
            $table->boolean('destaque_site')->default(false);
            $table->date('data_publicacao')->nullable();
            $table->date('data_expiracao')->nullable();

            // Auditoria
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Índices
            $table->index(['tipo', 'subtipo']);
            $table->index('perfil');
            $table->index('situacao');
            $table->index(['uf', 'cidade', 'bairro']);
            $table->index(['cidade', 'bairro']);
            $table->index('dormitorios');
            $table->index('suites');
            $table->index('garagens');
            $table->index('mobiliado');
            $table->index('tipo_negocio');
            $table->index(['tipo_negocio', 'preco_venda']);
            $table->index(['tipo_negocio', 'preco_aluguel']);
            $table->index('area_total');
            $table->index('aceita_permuta');
            $table->index('aceita_financiamento');
            $table->index('status');
            $table->index(['publicar_site', 'destaque_site']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imoveis');
    }
};
