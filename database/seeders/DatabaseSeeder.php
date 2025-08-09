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
            CondominioExemploSeeder::class,
            // TiposSubtiposSeeder é referência e não precisa ser executado
        ]);
    }
}
