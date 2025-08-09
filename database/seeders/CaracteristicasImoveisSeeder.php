<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CaracteristicasImoveisSeeder extends Seeder
{
    public function run(): void
    {
        $raw = [
            'Aceita Financiamento','Aceita Permuta','Adega','Alarme','Aprovado Ambiental','Aquecimento a gás','Aquecimento central','Aquecimento solar','Ar condicionado','Ar condicionado central','Área esportiva','Area Serviço','Armario Area De Servico','Armario Banheiro','Armario Closet','Armario Corredor','Armario Cozinha','Armario Dorm. Empregada','Armario Dormitorio','Armario Escritorio','Armario Home Theater','Armário na cozinha','Armario Sala','Banheira','Banheiro Empregada','Biblioteca','Cabeamento estruturado','Calefação','Campo Futebol','Churrasqueira','Cimento Queimado','Circuito de segurança','Condominio Fechado','Copa','Cozinha','Cozinha americana','Cozinha gourmet','Deck Molhado','Depósito','Deposito','Despensa','Destaque','Dormitorio Empregada','Dormitorio Reversivel','Elevador','Energia solar','Escritorio','Espaço Pet','Espaço verde','Exclusividade','Fechadura digital','Fgts','Forro de gesso','Forro de madeira','Forro de PVC','Gás central','Gás individual','Gerador elétrico','Hidro','Hidromassagem','Hidrômetro individual','Interfone','Internet','Isolamento acústico','Jardim Inverno','Lareira','Lavabo','Lavanderia','Litoral','Locado','Mezanino','Mobiliado','Ofuro','Pe Direito Duplo','Piscina','Piso Ardosia','Piso Granito','Piso Laminado','Piso Marmore','Piso Porcelanato','Piso Taboa','Piso Taco','Placa','Portais','Portao','Portaria','Projeto Aprovado','Quadra Poliesportiva','Ronda/Vigilância','Rua asfaltada','Sacada','Sauna','Sem Comdomínio','Sistema de alarme','Site','Solarium','Terraco','Tv Cabo','Varanda','Varanda Gourmet','Vestiario','Vigia','Vista exterior','Vista para a montanha','Vista para o lago','Zelador'
        ];

        $now = now();
        $adminId = 1; // já existe

        // Normalização leve (trim) e deduplicação mantendo rótulo original
        $names = array_values(array_unique(array_map(fn($n) => trim($n), $raw)));

        // Mapa desejado por chave canônica (lower(trim(nome)))
        $desiredKeys = array_map(fn($n) => mb_strtolower(trim($n)), $names);

        // Carrega existentes do escopo para detectar colisões e contagens
        $existingAll = DB::table('caracteristicas')
            ->where('escopo', 'IMOVEL')
            ->get(['id','nome','sistema']);
        $existingMap = [];
        foreach ($existingAll as $e) {
            $existingMap[mb_strtolower(trim($e->nome))] = (bool)$e->sistema;
        }

        // Detecta colisões com sistema=false e prepara log
        $skipCollisions = [];
        foreach ($desiredKeys as $i => $k) {
            if (array_key_exists($k, $existingMap) && $existingMap[$k] === false) {
                $skipCollisions[$k] = $names[$i];
            }
        }

        // Monta linhas, pulando colisões com sistema=false
        $rows = [];
        foreach ($names as $i => $nome) {
            $key = $desiredKeys[$i];
            if (isset($skipCollisions[$key])) {
                continue; // não sobrescrever registros do usuário
            }
            $rows[] = [
                'nome' => $nome,
                'escopo' => 'IMOVEL',
                'sistema' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $adminId,
                'updated_by' => null,
            ];
        }

        // Estatísticas antes do upsert
        $presentBefore = 0;
        foreach ($desiredKeys as $k) {
            if (array_key_exists($k, $existingMap)) {
                $presentBefore++;
            }
        }
        $skipped = count($skipCollisions);

        // Upsert idempotente (preserva created_at); não atualiza 'nome' nem 'escopo'
        DB::table('caracteristicas')->upsert(
            $rows,
            ['nome', 'escopo'],
            ['sistema', 'updated_at', 'updated_by']
        );

        $totalDesejados = count($names);
        $inserted = max(0, $totalDesejados - $presentBefore - $skipped);
        $updated = max(0, count($rows) - $inserted);

        if (isset($this->command)) {
            if ($skipped > 0) {
                $this->command->warn("Caracteristicas (IMOVEL): {$skipped} colisões encontradas com sistema=false (não sobrescritas)");
            }
            $this->command->info("Caracteristicas (IMOVEL): inseridos={$inserted}, atualizados={$updated}");
        }
    }
}
