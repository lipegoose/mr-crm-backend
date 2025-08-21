<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bairros', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->foreignId('cidade_id')->constrained('cidades')->onDelete('cascade');
            
            // Auditoria
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Índices
            $table->index('nome');
            $table->index('cidade_id');
            $table->unique(['nome', 'cidade_id']); // Evitar duplicação de bairro na mesma cidade
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bairros');
    }
};
