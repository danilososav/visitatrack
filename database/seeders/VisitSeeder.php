<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPhoto;
use App\Models\VisitTrackPoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $workers = User::query()->where('role', 'worker')->get();
        $companies = Company::all();
        $machines = Machine::all();
        $activities = Activity::all();

        // Historical, completed client visits
        collect(range(1, 30))->each(function () use ($workers, $companies, $activities) {
            $visit = Visit::factory()->create([
                'worker_id' => $workers->random()->id,
                'company_id' => $companies->random()->id,
            ]);
            $visit->activities()->attach($activities->random(rand(1, 3))->pluck('id'));
        });

        // Historical, completed machine jobs
        collect(range(1, 12))->each(function () use ($workers, $machines, $activities) {
            $visit = Visit::factory()->machineJob()->create([
                'worker_id' => $workers->random()->id,
                'machine_id' => $machines->random()->id,
            ]);
            $visit->activities()->attach($activities->random(rand(1, 2))->pluck('id'));
        });

        // Pending approval — visible in the admin review queue
        collect(range(1, 4))->each(function (int $i) use ($workers, $companies, $activities) {
            $visit = Visit::factory()->pendingApproval()->create([
                'worker_id' => $workers->random()->id,
                'company_id' => $companies->random()->id,
            ]);
            $visit->update([
                'worker_signature_path' => $this->placeholderSignature($visit->id, 'worker'),
                'second_signer_name' => fake()->name(),
                'second_signer_path' => $this->placeholderSignature($visit->id, 'second'),
            ]);
            $visit->activities()->attach($activities->random(rand(1, 2))->pluck('id'));
            $this->seedTrackPoints($visit);
            $this->seedPhoto($visit);
        });

        // Currently in progress — shown live on the dashboard
        collect(range(1, 3))->each(function () use ($workers, $companies, $activities) {
            $visit = Visit::factory()->inProgress()->create([
                'worker_id' => $workers->random()->id,
                'company_id' => $companies->random()->id,
            ]);
            $visit->activities()->attach($activities->random(rand(1, 2))->pluck('id'));
            $this->seedTrackPoints($visit);
        });

        // A couple of cancelled visits for realism
        collect(range(1, 2))->each(fn () => Visit::factory()->cancelled()->create([
            'worker_id' => $workers->random()->id,
            'company_id' => $companies->random()->id,
        ]));
    }

    private function seedTrackPoints(Visit $visit): void
    {
        $steps = 10;

        for ($i = 0; $i <= $steps; $i++) {
            $ratio = $i / $steps;
            VisitTrackPoint::create([
                'visit_id' => $visit->id,
                'lat' => $visit->departed_base_lat + ($visit->arrived_client_lat - $visit->departed_base_lat) * $ratio + fake()->randomFloat(6, -0.001, 0.001),
                'lng' => $visit->departed_base_lng + ($visit->arrived_client_lng - $visit->departed_base_lng) * $ratio + fake()->randomFloat(6, -0.001, 0.001),
                'leg' => 'to_client',
                'recorded_at' => now()->subMinutes($steps - $i),
            ]);
        }
    }

    private function placeholderSignature(string $visitId, string $who): string
    {
        $path = "signatures/{$visitId}-{$who}.png";
        Storage::disk('visits')->put($path, $this->placeholderPngContents());

        return $path;
    }

    private function seedPhoto(Visit $visit): void
    {
        $path = "photos/{$visit->id}-1.png";
        Storage::disk('visits')->put($path, $this->placeholderPngContents());

        VisitPhoto::create([
            'visit_id' => $visit->id,
            'step' => $visit->status,
            'disk_path' => $path,
            'uploaded_at' => now(),
        ]);
    }

    private function placeholderPngContents(): string
    {
        // 1x1 transparent PNG — just enough to exercise the storage/serving path in the demo.
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }
}
