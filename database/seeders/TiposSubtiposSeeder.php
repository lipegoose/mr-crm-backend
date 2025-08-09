<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Catálogo de referência para Tipos/Subtipos.
 * NÃO EXECUTA INSERÇÕES NO BANCO.
 */
class TiposSubtiposSeeder extends Seeder
{
    public function run(): void
    {
        $tiposSubtipos = [
            ['value' => 'apartamento', 'label' => 'Apartamento', 'subtipo' => null],
            ['value' => 'apartamento-cobertura', 'label' => 'Apartamento - Cobertura', 'subtipo' => 'Cobertura'],
            ['value' => 'apartamento-duplex', 'label' => 'Apartamento - Duplex', 'subtipo' => 'Duplex'],
            ['value' => 'casa', 'label' => 'Casa', 'subtipo' => null],
            ['value' => 'casa-condominio', 'label' => 'Casa em Condomínio', 'subtipo' => 'Em Condomínio'],
            ['value' => 'chacara', 'label' => 'Chácara', 'subtipo' => null],
            ['value' => 'comercial-loja', 'label' => 'Comercial - Loja', 'subtipo' => 'Loja'],
            ['value' => 'comercial-sala', 'label' => 'Comercial - Sala', 'subtipo' => 'Sala'],
            ['value' => 'comercial-galpao', 'label' => 'Comercial - Galpão', 'subtipo' => 'Galpão'],
            ['value' => 'terreno', 'label' => 'Terreno', 'subtipo' => null],
            ['value' => 'fazenda', 'label' => 'Fazenda', 'subtipo' => null],
            ['value' => 'sitio', 'label' => 'Sítio', 'subtipo' => null],
        ];

        // Use este catálogo para validações/configs. Não persiste dados.
        if (isset($this->command)) {
            $this->command->info('TiposSubtiposSeeder: catálogo carregado para referência (sem inserções).');
        }
    }
}
