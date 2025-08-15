<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imoveis', function (Blueprint $table) {
            // Renomear o campo unidade_medida para unidade_medida_area_total
            $table->renameColumn('unidade_medida', 'unidade_medida_area_total');
            
            // Adicionar os novos campos de unidade de medida
            $table->enum('unidade_medida_area_construida', ['m²', 'ha'])->default('m²')->after('area_construida');
            $table->enum('unidade_medida_area_privativa', ['m²', 'ha'])->default('m²')->after('area_privativa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imoveis', function (Blueprint $table) {
            // Remover os novos campos
            $table->dropColumn('unidade_medida_area_construida');
            $table->dropColumn('unidade_medida_area_privativa');
            
            // Renomear de volta para o nome original
            $table->renameColumn('unidade_medida_area_total', 'unidade_medida');
        });
    }
};
