<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('section_item_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_item_id')->constrained('section_items')->cascadeOnDelete();
            $table->string('titulo', 255)->nullable();
            $table->string('caminho', 255)->comment('Caminho relativo para o arquivo da imagem');
            $table->unsignedInteger('ordem')->default(0)->comment('Ordem de exibição da imagem');
            $table->boolean('principal')->default(false)->comment('Indica se é a imagem principal do item');
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Índices
            $table->index('section_item_id');
            $table->index(['section_item_id', 'principal']);
            $table->index(['section_item_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_item_photos');
    }
};
