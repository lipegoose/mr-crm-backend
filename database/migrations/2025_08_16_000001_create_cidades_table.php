<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->char('uf', 2);
            
            // Auditoria
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Índices
            $table->index('nome');
            $table->index('uf');
            $table->unique(['nome', 'uf']); // Evitar duplicação de cidade na mesma UF
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cidades');
    }
};
