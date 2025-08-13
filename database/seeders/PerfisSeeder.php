<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PerfisSeeder extends Seeder
{
    public function run(): void
    {
        // Só roda se a tabela estiver vazia
        $isEmpty = DB::table('perfis')->count() === 0;
        if (!$isEmpty) {
            if (isset($this->command)) {
                $this->command->info('PerfisSeeder: ignorado (tabela não vazia).');
            }
            return;
        }

        $now = Carbon::now();
        $adminId = 1; // admin já existe

        // Lista de perfis a serem criados
        $perfis = [
            ['value' => 'RESIDENCIAL', 'label' => 'Residencial'],
            ['value' => 'COMERCIAL', 'label' => 'Comercial'],
            ['value' => 'RESIDENCIAL-COMERCIAL', 'label' => 'Residencial/Comercial'],
            ['value' => 'INDUSTRIAL', 'label' => 'Industrial'],
            ['value' => 'RURAL', 'label' => 'Rural'],
            ['value' => 'TEMPORADA', 'label' => 'Temporada'],
        ];

        // Inserir cada perfil
        foreach ($perfis as $perfil) {
            DB::table('perfis')->insert([
                'value' => $perfil['value'],
                'label' => $perfil['label'],
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $adminId,
                'updated_by' => null,
            ]);
        }

        if (isset($this->command)) {
            $this->command->info('Perfis criados com sucesso: ' . count($perfis));
        }
    }
}
