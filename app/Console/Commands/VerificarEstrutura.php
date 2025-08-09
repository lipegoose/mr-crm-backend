<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarEstrutura extends Command
{
    /**
     * Nome do comando.
     *
     * @var string
     */
    protected $signature = 'estrutura:verificar {tabela?}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Verifica a estrutura de uma tabela no banco de dados';

    /**
     * Executa o comando.
     *
     * @return int
     */
    public function handle()
    {
        $tabela = $this->argument('tabela') ?: 'imoveis';
        
        $this->info("Verificando estrutura da tabela {$tabela}...");
        
        // Obter estrutura da tabela
        $colunas = DB::select("SHOW COLUMNS FROM {$tabela}");
        
        $this->table(
            ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'],
            collect($colunas)->map(function ($coluna) {
                return [
                    'Field' => $coluna->Field,
                    'Type' => $coluna->Type,
                    'Null' => $coluna->Null,
                    'Key' => $coluna->Key,
                    'Default' => $coluna->Default,
                    'Extra' => $coluna->Extra,
                ];
            })->toArray()
        );
        
        // Verificar se há colunas do tipo ENUM e mostrar os valores possíveis
        foreach ($colunas as $coluna) {
            if (strpos($coluna->Type, 'enum') === 0) {
                $this->info("Valores possíveis para {$coluna->Field}:");
                preg_match('/enum\((.*)\)/', $coluna->Type, $matches);
                $valores = explode(',', $matches[1]);
                $valores = array_map(function ($valor) {
                    return trim($valor, "'");
                }, $valores);
                $this->line(implode(', ', $valores));
            }
        }
        
        return Command::SUCCESS;
    }
}
