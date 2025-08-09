<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CaracteristicasCondominiosSeeder extends Seeder
{
    public function run(): void
    {
        $raw = [
            'Academia','Área de festas','Área gourmet','Bicicletário','Brinquedoteca','Campo de futebol','Churrasqueira coletiva','Cinema','Circuito de segurança','Elevador','Espaço coworking','Espaço gourmet','Espaço pet','Espaço zen','Estacionamento para visitantes','Fitness','Gerador','Guarita','Jardim','Lavanderia coletiva','Piscina adulto','Piscina infantil','Playground','Portaria 24h','Portaria eletrônica','Porteiro eletrônico','Quadra de esportes','Quadra de tênis','Quadra poliesportiva','Salão de festas','Salão de jogos','Sauna','Segurança 24h','Spa','Vagas para visitantes','Wi-fi nas áreas comuns'
        ];

        $now = Carbon::now();
        $adminId = 1; // já existe

        // Normalização leve (trim) e deduplicação mantendo rótulo original
        $names = array_values(array_unique(array_map(fn($n) => trim($n), $raw)));

        $desiredKeys = array_map(fn($n) => mb_strtolower(trim($n)), $names);

        $existingAll = DB::table('caracteristicas')
            ->where('escopo', 'CONDOMINIO')
            ->get(['id','nome','sistema']);
        $existingMap = [];
        foreach ($existingAll as $e) {
            $existingMap[mb_strtolower(trim($e->nome))] = (bool)$e->sistema;
        }

        $skipCollisions = [];
        foreach ($desiredKeys as $i => $k) {
            if (array_key_exists($k, $existingMap) && $existingMap[$k] === false) {
                $skipCollisions[$k] = $names[$i];
            }
        }

        $rows = [];
        foreach ($names as $i => $nome) {
            $key = $desiredKeys[$i];
            if (isset($skipCollisions[$key])) {
                continue; // não sobrescrever registros do usuário
            }
            $rows[] = [
                'nome' => $nome,
                'escopo' => 'CONDOMINIO',
                'sistema' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $adminId,
                'updated_by' => null,
            ];
        }

        $presentBefore = 0;
        foreach ($desiredKeys as $k) {
            if (array_key_exists($k, $existingMap)) {
                $presentBefore++;
            }
        }
        $skipped = count($skipCollisions);

        DB::table('caracteristicas')->upsert(
            $rows,
            ['nome','escopo'],
            ['sistema','updated_at','updated_by']
        );

        $totalDesejados = count($names);
        $inserted = max(0, $totalDesejados - $presentBefore - $skipped);
        $updated = max(0, count($rows) - $inserted);

        if (isset($this->command)) {
            if ($skipped > 0) {
                $this->command->warn("Caracteristicas (CONDOMINIO): {$skipped} colisões encontradas com sistema=false (não sobrescritas)");
            }
            $this->command->info("Caracteristicas (CONDOMINIO): inseridos={$inserted}, atualizados={$updated}");
        }
    }
}
