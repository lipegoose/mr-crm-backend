<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProximidadesSeeder extends Seeder
{
    public function run(): void
    {
        $raw = [
            'Academia','Aeroporto','Banco','Biblioteca','Centro comercial','Cinema','Escola','Estação de metrô','Estação de trem','Farmácia','Hospital','Padaria','Parque','Ponto de ônibus','Posto de gasolina','Praia','Restaurante','Shopping','Supermercado','Teatro','Universidade'
        ];

        $now = now();
        $adminId = 1; // já existe

        // Normalização leve (trim) e deduplicação mantendo rótulo original
        $names = array_values(array_unique(array_map(fn($n) => trim($n), $raw)));

        $desiredKeys = array_map(fn($n) => mb_strtolower(trim($n)), $names);

        // Carregar existentes para colisões (por nome)
        $existingAll = DB::table('proximidades')->get(['id','nome','sistema']);
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

        DB::table('proximidades')->upsert(
            $rows,
            ['nome'],
            ['sistema','updated_at','updated_by']
        );

        $totalDesejados = count($names);
        $inserted = max(0, $totalDesejados - $presentBefore - $skipped);
        $updated = max(0, count($rows) - $inserted);

        if (isset($this->command)) {
            if ($skipped > 0) {
                $this->command->warn("Proximidades: {$skipped} colisões encontradas com sistema=false (não sobrescritas)");
            }
            $this->command->info("Proximidades: inseridos={$inserted}, atualizados={$updated}");
        }
    }
}
