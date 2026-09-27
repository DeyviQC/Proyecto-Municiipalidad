<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Orden de ejecución: roles -> usuarios -> actas de prueba (30 actas
     * ficticias con PDF de demostración real almacenado en storage/).
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ActaSeeder::class,
        ]);
    }
}
