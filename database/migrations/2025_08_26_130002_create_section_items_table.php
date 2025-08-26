<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('titulo', 255);
            $table->string('subtitulo', 255)->nullable();
            $table->text('descricao')->nullable();
            $table->string('url_link', 500)->nullable()->comment('Pode ser âncora (#...) ou URL absoluta');
            $table->string('texto_url', 255)->nullable();
            $table->boolean('botao')->default(false);
            $table->string('url_amigavel', 255)->nullable()->comment('Slug editável amigável para SEO do item (único por seção)');
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['section_id', 'ordem']);
            $table->index(['ativo', 'ordem']);
            $table->unique(['section_id', 'url_amigavel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_items');
    }
};
