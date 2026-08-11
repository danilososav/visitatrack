<?php

namespace App\Livewire\Portal;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Machine;
use App\Models\Visit;
use App\Models\VisitPhoto;
use App\Models\VisitTrackPoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.portal')]
class VisitWizard extends Component
{
    use WithFileUploads;

    public ?string $visitId = null;

    public string $step = 'setup';

    // --- setup form state ---
    public string $type = 'client_visit';

    public ?int $companyId = null;

    public ?int $machineId = null;

    public string $ovNumber = '';

    public string $otNumber = '';

    public array $activityIds = [];

    public string $notes = '';

    public ?string $ovHint = null;

    // --- photo upload state ---
    public $newPhotos = [];

    // --- second signer ---
    public string $secondSignerName = '';

    public function mount(?Visit $visit = null): void
    {
        if ($visit && $visit->exists) {
            abort_unless($visit->worker_id === Auth::id(), 403);

            $this->visitId = $visit->id;
            $this->step = $visit->status;
            $this->secondSignerName = $visit->second_signer_name ?? '';
        }
    }

    public function getVisitProperty(): ?Visit
    {
        return $this->visitId ? Visit::find($this->visitId) : null;
    }

    public function getCompaniesProperty()
    {
        return Company::orderBy('name')->get();
    }

    public function getMachinesProperty()
    {
        return Machine::where('is_active', true)->orderBy('name')->get();
    }

    public function getActivitiesProperty()
    {
        return Activity::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function updatedOvNumber(): void
    {
        $this->ovHint = null;

        if ($this->type !== 'machine_job' || strlen($this->ovNumber) < 2) {
            return;
        }

        $match = Visit::query()
            ->where('type', 'machine_job')
            ->where('ov_number', $this->ovNumber)
            ->latest('created_at')
            ->first();

        if ($match) {
            $this->ovHint = 'Ya se registró OV '.$this->ovNumber.' — '.($match->machine?->name ?? 'sin máquina asociada');
        }
    }

    public function applyOvHint(): void
    {
        $match = Visit::query()
            ->where('type', 'machine_job')
            ->where('ov_number', $this->ovNumber)
            ->latest('created_at')
            ->first();

        if ($match) {
            $this->machineId = $match->machine_id;
            $this->activityIds = $match->activities()->pluck('activities.id')->all();
        }

        $this->ovHint = null;
    }

    /**
     * Step 1 — depart the base, creating the visit record.
     */
    public function startVisit(float $lat, float $lng): void
    {
        $this->validate([
            'type' => ['required', 'in:client_visit,machine_job'],
            'companyId' => ['required_if:type,client_visit', 'nullable', 'exists:companies,id'],
            'machineId' => ['required_if:type,machine_job', 'nullable', 'exists:machines,id'],
        ]);

        $visit = Visit::create([
            'id' => (string) Str::uuid(),
            'worker_id' => Auth::id(),
            'type' => $this->type,
            'status' => Visit::STATUS_TRAVELING_TO,
            'company_id' => $this->type === 'client_visit' ? $this->companyId : null,
            'machine_id' => $this->type === 'machine_job' ? $this->machineId : null,
            'ov_number' => $this->ovNumber ?: null,
            'ot_number' => $this->otNumber ?: null,
            'notes' => $this->notes ?: null,
            'departed_base_at' => now(),
            'departed_base_lat' => $lat,
            'departed_base_lng' => $lng,
        ]);

        if (! empty($this->activityIds)) {
            $visit->activities()->sync($this->activityIds);
        }

        $this->visitId = $visit->id;
        $this->step = Visit::STATUS_TRAVELING_TO;
    }

    /**
     * Step 2 — arrive at the client/machine site.
     */
    public function confirmArrival(float $lat, float $lng): void
    {
        $this->visit?->update([
            'status' => Visit::STATUS_AT_CLIENT,
            'arrived_client_at' => now(),
            'arrived_client_lat' => $lat,
            'arrived_client_lng' => $lng,
        ]);

        $this->step = Visit::STATUS_AT_CLIENT;
    }

    /**
     * Step 3 — depart the client/machine site, heading back.
     */
    public function confirmDeparture(float $lat, float $lng): void
    {
        $this->visit?->update([
            'status' => Visit::STATUS_TRAVELING_BACK,
            'departed_client_at' => now(),
            'departed_client_lat' => $lat,
            'departed_client_lng' => $lng,
        ]);

        $this->step = Visit::STATUS_TRAVELING_BACK;
    }

    /**
     * Step 4 — arrive back at base; awaiting signatures.
     */
    public function confirmReturn(float $lat, float $lng): void
    {
        $this->visit?->update([
            'status' => Visit::STATUS_PENDING_APPROVAL,
            'arrived_base_at' => now(),
            'arrived_base_lat' => $lat,
            'arrived_base_lng' => $lng,
        ]);

        $this->step = Visit::STATUS_PENDING_APPROVAL;
    }

    /**
     * Batched GPS breadcrumb points from the browser's watchPosition buffer.
     *
     * @param  array<int, array{lat: float, lng: float, ts: string}>  $points
     */
    public function recordTrackPoints(array $points, string $leg): void
    {
        if (! $this->visit || empty($points)) {
            return;
        }

        $rows = array_map(fn (array $p) => [
            'visit_id' => $this->visit->id,
            'lat' => $p['lat'],
            'lng' => $p['lng'],
            'leg' => $leg,
            'recorded_at' => $p['ts'],
        ], $points);

        VisitTrackPoint::insert($rows);
    }

    public function savePhotos(): void
    {
        if (! $this->visit || empty($this->newPhotos)) {
            return;
        }

        foreach ($this->newPhotos as $photo) {
            $path = $photo->store('photos', 'visits');

            VisitPhoto::create([
                'visit_id' => $this->visit->id,
                'step' => $this->visit->status,
                'disk_path' => $path,
                'uploaded_at' => now(),
            ]);
        }

        $this->newPhotos = [];
    }

    public function saveSignature(string $who, string $dataUrl): void
    {
        if (! $this->visit || ! in_array($who, ['worker', 'second'], true)) {
            return;
        }

        if ($who === 'second' && blank($this->secondSignerName)) {
            $this->addError('secondSignerName', 'Ingresá el nombre de quien firma.');

            return;
        }

        [, $encoded] = explode(',', $dataUrl, 2) + [null, null];
        if (! $encoded) {
            return;
        }

        $path = "signatures/{$this->visit->id}-{$who}.png";
        Storage::disk('visits')->put($path, base64_decode($encoded));

        $this->visit->update(
            $who === 'worker'
                ? ['worker_signature_path' => $path]
                : ['second_signer_path' => $path, 'second_signer_name' => $this->secondSignerName]
        );

        $this->dispatch('signature-saved', who: $who);
    }

    public function finish(): void
    {
        $this->redirectRoute('portal.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.portal.visit-wizard', [
            'visit' => $this->visit,
        ]);
    }
}
