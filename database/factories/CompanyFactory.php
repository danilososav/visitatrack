<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    private const CIUDADES = [
        'Asunción', 'Ciudad del Este', 'Encarnación', 'Luque', 'San Lorenzo',
        'Fernando de la Mora', 'Lambaré', 'Capiatá', 'Ñemby', 'Itauguá',
    ];

    private const CALLES = [
        'Av. Mariscal López', 'Av. España', 'Av. Eusebio Ayala', 'Av. Aviadores del Chaco',
        'Calle Palma', 'Av. Santísima Trinidad', 'Av. Fernando de la Mora',
        'Av. Denis Roa', 'Calle 14 de Mayo', 'Av. Gral. Santos',
    ];

    public function definition(): array
    {
        $calle = fake()->randomElement(self::CALLES);
        $ciudad = fake()->randomElement(self::CIUDADES);

        return [
            'name' => fake()->company(),
            'address' => "{$calle} {$this->faker->numberBetween(100, 3500)}, {$ciudad}",
            'contact_name' => fake()->name(),
            'contact_phone' => $this->paraguayPhone(),
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }

    private function paraguayPhone(): string
    {
        $prefijo = fake()->randomElement(['981', '982', '983', '984', '985', '991', '994', '995']);

        return '+595 '.$prefijo.' '.fake()->numerify('###').' '.fake()->numerify('###');
    }
}
