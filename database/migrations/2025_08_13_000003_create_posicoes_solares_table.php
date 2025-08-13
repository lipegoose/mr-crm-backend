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
        Schema::create('posicoes_solares', function (Blueprint $table) {
            $table->id();
            
            // Campos principais
            $table->string('value', 50)->unique()->comment('Valor único para identificação (ex: LESTE)');
            $table->string('label', 100)->comment('Texto de exibição (ex: Leste)');
            
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
        Schema::dropIfExists('posicoes_solares');
    }
};
