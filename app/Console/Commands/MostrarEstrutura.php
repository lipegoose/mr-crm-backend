<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MostrarEstrutura extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'db:estrutura {tabela : Nome da tabela para mostrar estrutura}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Mostra a estrutura de uma tabela do banco de dados';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $tabela = $this->argument('tabela');
        
        $this->info("Estrutura da tabela '{$tabela}':");
        
        $colunas = DB::select("SHOW COLUMNS FROM {$tabela}");
        
        $headers = ['Campo', 'Tipo', 'Nulo', 'Chave', 'Padrão', 'Extra'];
        $rows = [];
        
        foreach ($colunas as $coluna) {
            $rows[] = [
                $coluna->Field,
                $coluna->Type,
                $coluna->Null,
                $coluna->Key,
                $coluna->Default ?? 'NULL',
                $coluna->Extra,
            ];
        }
        
        $this->table($headers, $rows);
        
        // Mostrar foreign keys
        $this->info("\nForeign Keys da tabela '{$tabela}':");
        
        $fks = DB::select("
            SELECT
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM
                information_schema.KEY_COLUMN_USAGE
            WHERE
                TABLE_NAME = '{$tabela}'
                AND REFERENCED_TABLE_NAME IS NOT NULL
                AND TABLE_SCHEMA = DATABASE()
        ");
        
        if (empty($fks)) {
            $this->info("Nenhuma foreign key encontrada.");
        } else {
            $fkHeaders = ['Constraint', 'Coluna', 'Tabela Referenciada', 'Coluna Referenciada'];
            $fkRows = [];
            
            foreach ($fks as $fk) {
                $fkRows[] = [
                    $fk->CONSTRAINT_NAME,
                    $fk->COLUMN_NAME,
                    $fk->REFERENCED_TABLE_NAME,
                    $fk->REFERENCED_COLUMN_NAME,
                ];
            }
            
            $this->table($fkHeaders, $fkRows);
        }
    }
}
