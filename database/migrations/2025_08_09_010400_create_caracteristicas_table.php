<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caracteristicas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->enum('escopo', ['IMOVEL', 'CONDOMINIO'])->comment('Define se a característica é do imóvel ou do condomínio');
            $table->boolean('sistema')->default(true)->comment('True para características pré-definidas pelo sistema');
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Índices
            $table->unique(['nome', 'escopo']);
            $table->index('escopo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caracteristicas');
    }
};
