<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        $baseLat = fake()->latitude(-27.5, -22);
        $baseLng = fake()->longitude(-62, -54.3);
        $clientLat = $baseLat + fake()->randomFloat(5, -0.15, 0.15);
        $clientLng = $baseLng + fake()->randomFloat(5, -0.15, 0.15);

        $departedBase = fake()->dateTimeBetween('-60 days', '-1 days');
        $arrivedClient = (clone $departedBase)->modify('+'.fake()->numberBetween(15, 60).' minutes');
        $departedClient = (clone $arrivedClient)->modify('+'.fake()->numberBetween(30, 240).' minutes');
        $arrivedBase = (clone $departedClient)->modify('+'.fake()->numberBetween(15, 60).' minutes');

        return [
            'worker_id' => User::factory()->worker(),
            'type' => Visit::TYPE_CLIENT_VISIT,
            'status' => Visit::STATUS_COMPLETED,
            'company_id' => Company::factory(),
            'machine_id' => null,
            'ov_number' => fake()->boolean(60) ? (string) fake()->numberBetween(1000, 9999) : null,
            'ot_number' => fake()->boolean(40) ? (string) fake()->numberBetween(1000, 9999) : null,
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,

            'departed_base_at' => $departedBase,
            'departed_base_lat' => $baseLat,
            'departed_base_lng' => $baseLng,

            'arrived_client_at' => $arrivedClient,
            'arrived_client_lat' => $clientLat,
            'arrived_client_lng' => $clientLng,

            'departed_client_at' => $departedClient,
            'departed_client_lat' => $clientLat,
            'departed_client_lng' => $clientLng,

            'arrived_base_at' => $arrivedBase,
            'arrived_base_lat' => $baseLat,
            'arrived_base_lng' => $baseLng,

            'approved_at' => (clone $arrivedBase)->modify('+'.fake()->numberBetween(5, 120).' minutes'),
        ];
    }

    public function machineJob(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Visit::TYPE_MACHINE_JOB,
            'company_id' => null,
            'machine_id' => Machine::factory(),
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Visit::STATUS_PENDING_APPROVAL,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function inProgress(): static
    {
        $status = fake()->randomElement(Visit::ACTIVE_STATUSES);

        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'departed_client_at' => $status === Visit::STATUS_TRAVELING_TO ? null : $attributes['departed_client_at'],
            'arrived_base_at' => null,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Visit::STATUS_CANCELLED,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }
}
