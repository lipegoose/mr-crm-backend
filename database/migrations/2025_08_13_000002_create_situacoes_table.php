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
        Schema::create('situacoes', function (Blueprint $table) {
            $table->id();
            
            // Campos principais
            $table->string('value', 50)->unique()->comment('Valor único para identificação (ex: PRONTO)');
            $table->string('label', 100)->comment('Texto de exibição (ex: Pronto para morar)');
            
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
        Schema::dropIfExists('situacoes');
    }
};
