<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Ferretería San Roque',
            'Supermercados La Paraguaya',
            'Transportes Ñandutí',
            'Distribuidora Itapúa',
            'Comercial Guaraní S.A.',
            'Textiles Encarnación',
            'Agroindustrial del Este',
            'Construcciones Yguazú',
        ])->each(fn (string $name) => Company::factory()->create(['name' => $name]));
    }
}
