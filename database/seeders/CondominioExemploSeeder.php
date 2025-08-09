<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CondominioExemploSeeder extends Seeder
{
    public function run(): void
    {
        // Só roda se a tabela estiver vazia OU se a flag SEED_EXEMPLOS estiver habilitada
        $allow = (bool) filter_var(env('SEED_EXEMPLOS', false), FILTER_VALIDATE_BOOLEAN);
        $isEmpty = DB::table('condominios')->count() === 0;
        if (!($allow || $isEmpty)) {
            if (isset($this->command)) {
                $this->command->info('CondominioExemploSeeder: ignorado (sem flag e tabela não vazia).');
            }
            return;
        }

        $now = now();
        $adminId = 1; // admin já existe

        $condominioId = DB::table('condominios')->insertGetId([
            'nome' => 'Condomínio Residencial Exemplo',
            'descricao' => 'Condomínio de alto padrão com diversas comodidades e excelente localização.',
            'cep' => '30130110',
            'uf' => 'MG',
            'cidade' => 'Belo Horizonte',
            'bairro' => 'Savassi',
            'logradouro' => 'Rua Paraíba',
            'numero' => '1400',
            'latitude' => -19.935214,
            'longitude' => -43.938561,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $adminId,
            'updated_by' => null,
        ]);

        $caracteristicas = [
            'Portaria 24h',
            'Piscina adulto',
            'Academia',
            'Salão de festas',
            'Playground',
        ];

        $linked = 0;
        foreach ($caracteristicas as $nome) {
            $caracteristicaId = DB::table('caracteristicas')
                ->where('escopo', 'CONDOMINIO')
                ->whereRaw('LOWER(TRIM(nome)) = LOWER(TRIM(?))', [$nome])
                ->value('id');

            if ($caracteristicaId) {
                // Evitar duplicar pivot
                $exists = DB::table('condominios_caracteristicas')
                    ->where('condominio_id', $condominioId)
                    ->where('caracteristica_id', $caracteristicaId)
                    ->exists();
                if (!$exists) {
                    DB::table('condominios_caracteristicas')->insert([
                        'condominio_id' => $condominioId,
                        'caracteristica_id' => $caracteristicaId,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'created_by' => $adminId,
                        'updated_by' => null,
                    ]);
                    $linked++;
                }
            }
        }

        if (isset($this->command)) {
            $this->command->info("Condomínio exemplo criado (ID={$condominioId}); características vinculadas={$linked}");
        }
    }
}
