<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    protected $model = Machine::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Impresora Digital X100', 'Cortadora Láser Pro', 'Router CNC Modelo A',
                'Plotter de Corte 3000', 'Soldadora Industrial S200', 'Compresor Demo 500',
            ]).' #'.fake()->numberBetween(1, 9),
            'code' => strtoupper(fake()->bothify('MQ-####')),
            'category' => fake()->randomElement(['Impresión', 'Corte', 'Soldadura', 'Movilidad']),
            'is_active' => true,
        ];
    }
}
