<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Acme Corp', 'Cliente Demo S.A.', 'Globex Industries', 'Constructora Modelo',
            'Distribuidora Central', 'Grupo Horizonte', 'Comercial del Sur', 'Metalúrgica Ejemplo',
        ])->each(fn (string $name) => Company::factory()->create(['name' => $name]));
    }
}
