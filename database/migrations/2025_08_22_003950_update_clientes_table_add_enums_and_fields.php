<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalizar dados existentes antes de alterar tipos
        DB::statement("UPDATE clientes SET status = 'ATIVO' WHERE status IS NULL OR status NOT IN ('ATIVO','INATIVO')");
        DB::statement("UPDATE clientes SET tipo = 'PESSOA_FISICA' WHERE tipo IS NULL OR tipo NOT IN ('PESSOA_FISICA','PESSOA_JURIDICA')");

        // Alterar colunas para ENUM (MariaDB/MySQL)
        DB::statement("ALTER TABLE clientes MODIFY COLUMN status ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO'");
        DB::statement("ALTER TABLE clientes MODIFY COLUMN tipo ENUM('PESSOA_FISICA','PESSOA_JURIDICA') NOT NULL DEFAULT 'PESSOA_FISICA'");

        // Adicionar novos campos enum opcionais
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('categoria', ['cliente','prospecto','lead'])->nullable()->after('status');
            $table->enum('origem_captacao', ['site','indicacao','redes_sociais','anuncio','outro'])->nullable()->after('categoria');
        });
    }

    public function down(): void
    {
        // Remover novos campos
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'origem_captacao')) {
                $table->dropColumn('origem_captacao');
            }
            if (Schema::hasColumn('clientes', 'categoria')) {
                $table->dropColumn('categoria');
            }
        });

        // Reverter ENUMs para VARCHARs genéricos (fallback seguro)
        DB::statement("ALTER TABLE clientes MODIFY COLUMN status VARCHAR(20) NULL");
        DB::statement("ALTER TABLE clientes MODIFY COLUMN tipo VARCHAR(20) NULL");
    }
};
