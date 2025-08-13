<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PosicoesSeeder extends Seeder
{
    public function run(): void
    {
        // Só roda se a tabela estiver vazia
        $isEmpty = DB::table('posicoes_solares')->count() === 0;
        if (!$isEmpty) {
            if (isset($this->command)) {
                $this->command->info('PosicoesSeeder: ignorado (tabela não vazia).');
            }
            return;
        }

        $now = Carbon::now();
        $adminId = 1; // admin já existe

        // Lista de posições solares a serem criadas
        $posicoes = [
            ['value' => 'LESTE', 'label' => 'Leste'],
            ['value' => 'OESTE', 'label' => 'Oeste'],
            ['value' => 'NORTE', 'label' => 'Norte'],
            ['value' => 'SUL', 'label' => 'Sul'],
            ['value' => 'NORDESTE', 'label' => 'Nordeste'],
            ['value' => 'SUDESTE', 'label' => 'Sudeste'],
            ['value' => 'SUDOESTE', 'label' => 'Sudoeste'],
            ['value' => 'NOROESTE', 'label' => 'Noroeste'],
            ['value' => 'SOL-MANHA', 'label' => 'Sol da manhã'],
            ['value' => 'SOL-TARDE', 'label' => 'Sol da tarde'],
            ['value' => 'SOL-MANHA-TARDE', 'label' => 'Sol da manhã e tarde'],
        ];

        // Inserir cada posição solar
        foreach ($posicoes as $posicao) {
            DB::table('posicoes_solares')->insert([
                'value' => $posicao['value'],
                'label' => $posicao['label'],
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $adminId,
                'updated_by' => null,
            ]);
        }

        if (isset($this->command)) {
            $this->command->info('Posições solares criadas com sucesso: ' . count($posicoes));
        }
    }
}
