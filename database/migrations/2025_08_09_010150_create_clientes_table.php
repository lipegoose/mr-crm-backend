<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa as migrações.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            
            // Informações básicas
            $table->string('nome', 100);
            $table->string('tipo', 20)->comment('FISICA ou JURIDICA');
            $table->string('cpf_cnpj', 20)->nullable()->unique();
            $table->string('rg_ie', 20)->nullable()->comment('RG (Pessoa Física) ou Inscrição Estadual (Pessoa Jurídica)');
            
            // Contato
            $table->string('email', 100)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            
            // Endereço
            $table->string('cep', 10)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('logradouro', 100)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 100)->nullable();
            
            // Informações adicionais
            $table->text('observacoes')->nullable();
            $table->string('status', 20)->default('ATIVO');
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverte as migrações.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
