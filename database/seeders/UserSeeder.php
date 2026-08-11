<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@visitatrack.test',
            'base_lat' => -25.2637,
            'base_lng' => -57.5759,
        ]);

        collect(['Lucía Benítez', 'Marcos Ferreira', 'Sofía Cabrera', 'Diego Villalba', 'Valentina Duarte'])
            ->each(fn (string $name, int $i) => User::factory()->worker()->create([
                'name' => $name,
                'email' => 'worker'.($i + 1).'@visitatrack.test',
                'base_lat' => -25.2637,
                'base_lng' => -57.5759,
            ]));
    }
}
