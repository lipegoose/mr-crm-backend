<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Executa as migrações.
     */
    public function up(): void
    {
        // Alterar a coluna situacao para permitir NULL e ajustar o comentário
        DB::statement("ALTER TABLE `imoveis` 
            CHANGE `situacao` `situacao` varchar(50) COLLATE 'utf8mb4_unicode_ci' NULL 
            COMMENT 'Situação: Pronto, Em construção, Na planta, etc.' AFTER `perfil`");
        
        // Alterar a coluna proprietario_id para permitir NULL
        DB::statement("ALTER TABLE `imoveis` 
            CHANGE `proprietario_id` `proprietario_id` bigint(20) unsigned NULL 
            AFTER `codigo_referencia_editado`");
    }

    /**
     * Reverte as migrações.
     */
    public function down(): void
    {
        // Reverte as alterações (assumindo que antes eram NOT NULL)
        DB::statement("ALTER TABLE `imoveis` 
            CHANGE `situacao` `situacao` varchar(50) COLLATE 'utf8mb4_unicode_ci' NOT NULL 
            COMMENT 'Situação: Pronto, Em construção, Na planta, etc.' AFTER `perfil`");
        
        DB::statement("ALTER TABLE `imoveis` 
            CHANGE `proprietario_id` `proprietario_id` bigint(20) unsigned NOT NULL 
            AFTER `codigo_referencia_editado`");
    }
};
