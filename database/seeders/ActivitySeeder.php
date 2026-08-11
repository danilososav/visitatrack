<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Instalación', 'Mantenimiento preventivo', 'Mantenimiento correctivo',
            'Inspección de rutina', 'Entrega de equipo', 'Retiro de equipo',
            'Capacitación al cliente', 'Relevamiento técnico', 'Reparación urgente',
            'Actualización de software', 'Limpieza de equipo', 'Calibración',
            'Prueba de funcionamiento', 'Diagnóstico de falla', 'Entrega de repuestos',
        ])->values()->each(fn (string $name, int $i) => Activity::factory()->create([
            'name' => $name,
            'sort_order' => $i + 1,
        ]));
    }
}
