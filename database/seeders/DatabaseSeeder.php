<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CaracteristicasImoveisSeeder::class,
            CaracteristicasCondominiosSeeder::class,
            ProximidadesSeeder::class,
            ClienteExemploSeeder::class,
            CondominioExemploSeeder::class,
            // Novos seeders para cadastros de imóveis
            PerfisSeeder::class,
            SituacoesSeeder::class,
            PosicoesSeeder::class,
            CidadesBairrosSeeder::class,
            // TiposSubtiposSeeder é referência e não precisa ser executado
        ]);
    }
}
