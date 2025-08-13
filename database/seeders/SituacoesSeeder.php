<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SituacoesSeeder extends Seeder
{
    public function run(): void
    {
        // Só roda se a tabela estiver vazia
        $isEmpty = DB::table('situacoes')->count() === 0;
        if (!$isEmpty) {
            if (isset($this->command)) {
                $this->command->info('SituacoesSeeder: ignorado (tabela não vazia).');
            }
            return;
        }

        $now = Carbon::now();
        $adminId = 1; // admin já existe

        // Lista de situações a serem criadas
        $situacoes = [
            ['value' => 'PRONTO', 'label' => 'Pronto para morar'],
            ['value' => 'CONSTRUCAO', 'label' => 'Em construção'],
            ['value' => 'PLANTA', 'label' => 'Na planta'],
            ['value' => 'REFORMA', 'label' => 'Em reforma'],
        ];

        // Inserir cada situação
        foreach ($situacoes as $situacao) {
            DB::table('situacoes')->insert([
                'value' => $situacao['value'],
                'label' => $situacao['label'],
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $adminId,
                'updated_by' => null,
            ]);
        }

        if (isset($this->command)) {
            $this->command->info('Situações criadas com sucesso: ' . count($situacoes));
        }
    }
}
