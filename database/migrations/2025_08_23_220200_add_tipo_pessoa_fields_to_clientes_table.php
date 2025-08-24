<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Pessoa Jurídica - identificação
            $table->string('razao_social', 150)->nullable()->after('nome');
            $table->string('nome_fantasia', 150)->nullable()->after('razao_social');

            // Datas relacionadas
            $table->date('data_nascimento')->nullable()->after('rg_ie');
            $table->date('data_fundacao')->nullable()->after('data_nascimento');

            // Pessoa Física - complementares
            $table->string('profissao', 100)->nullable()->after('data_fundacao');
            $table->enum('estado_civil', ['solteiro', 'casado', 'divorciado', 'viuvo', 'uniao_estavel'])->nullable()->after('profissao');
            $table->decimal('renda_mensal', 12, 2)->nullable()->after('estado_civil');

            // Pessoa Jurídica - complementares
            $table->string('ramo_atividade', 150)->nullable()->after('renda_mensal');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'razao_social',
                'nome_fantasia',
                'data_nascimento',
                'data_fundacao',
                'profissao',
                'estado_civil',
                'renda_mensal',
                'ramo_atividade',
            ]);
        });
    }
};
