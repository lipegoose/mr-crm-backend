<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique()->comment('Identificador URL-safe da seção (ex.: destaques, sobre, servicos, blog)');
            $table->string('titulo', 255);
            $table->string('subtitulo', 255)->nullable();
            $table->text('descricao')->nullable();
            $table->string('url_link', 500)->nullable()->comment('Pode ser âncora (#destaques) ou URL absoluta');
            $table->string('texto_url', 255)->nullable();
            $table->boolean('botao')->default(false);
            $table->string('url_amigavel', 255)->nullable()->unique()->comment('Slug editável amigável para SEO');
            $table->enum('template', ['destaques', 'sobre', 'servicos', 'blog'])->comment('Template de renderização no site público');
            $table->boolean('show_on_home')->default(false);
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['show_on_home', 'ordem']);
            $table->index(['template', 'ordem']);
            $table->index(['ativo', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
