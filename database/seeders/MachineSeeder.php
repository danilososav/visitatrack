<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'Excavadora Modelo X100', 'category' => 'Movimiento de tierra'],
            ['name' => 'Compresor Demo 3000', 'category' => 'Aire comprimido'],
            ['name' => 'Impresora Digital X100', 'category' => 'Impresión'],
            ['name' => 'Cortadora Láser Pro', 'category' => 'Corte'],
            ['name' => 'Router CNC Modelo A', 'category' => 'Corte'],
            ['name' => 'Plotter de Corte 3000', 'category' => 'Corte'],
            ['name' => 'Soldadora Industrial S200', 'category' => 'Soldadura'],
            ['name' => 'Generador Portátil G50', 'category' => 'Energía'],
        ])->each(fn (array $m) => Machine::factory()->create([
            'name' => $m['name'],
            'category' => $m['category'],
        ]));
    }
}
