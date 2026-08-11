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

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
