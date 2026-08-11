<?php

namespace App\Models;

use App\Support\Haversine;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'worker_id', 'type', 'status', 'company_id', 'machine_id', 'ov_number', 'ot_number', 'notes',
    'group_id', 'group_order', 'group_size',
    'departed_base_at', 'departed_base_lat', 'departed_base_lng',
    'arrived_client_at', 'arrived_client_lat', 'arrived_client_lng',
    'departed_client_at', 'departed_client_lat', 'departed_client_lng',
    'arrived_base_at', 'arrived_base_lat', 'arrived_base_lng',
    'worker_signature_path', 'second_signer_name', 'second_signer_path',
    'approved_by', 'approved_at',
])]
class Visit extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    public const TYPE_CLIENT_VISIT = 'client_visit';

    public const TYPE_MACHINE_JOB = 'machine_job';

    public const STATUS_TRAVELING_TO = 'traveling_to';

    public const STATUS_AT_CLIENT = 'at_client';

    public const STATUS_TRAVELING_BACK = 'traveling_back';

    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [
        self::STATUS_TRAVELING_TO,
        self::STATUS_AT_CLIENT,
        self::STATUS_TRAVELING_BACK,
    ];

    protected function casts(): array
    {
        return [
            'departed_base_at' => 'datetime',
            'arrived_client_at' => 'datetime',
            'departed_client_at' => 'datetime',
            'arrived_base_at' => 'datetime',
            'approved_at' => 'datetime',
            'departed_base_lat' => 'decimal:7',
            'departed_base_lng' => 'decimal:7',
            'arrived_client_lat' => 'decimal:7',
            'arrived_client_lng' => 'decimal:7',
            'departed_client_lat' => 'decimal:7',
            'departed_client_lng' => 'decimal:7',
            'arrived_base_lat' => 'decimal:7',
            'arrived_base_lng' => 'decimal:7',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'company_id', 'machine_id', 'ov_number', 'ot_number', 'notes', 'approved_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return BelongsTo<User, $this> */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Machine, $this> */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /** @return BelongsToMany<Activity, $this> */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'visit_activities');
    }

    /** @return HasMany<VisitPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class);
    }

    /** @return HasMany<VisitTrackPoint, $this> */
    public function trackPoints(): HasMany
    {
        return $this->hasMany(VisitTrackPoint::class)->orderBy('recorded_at');
    }

    public function totalTrackDistanceKm(): float
    {
        $points = $this->trackPoints;

        $total = 0.0;
        for ($i = 1; $i < $points->count(); $i++) {
            $prev = $points[$i - 1];
            $curr = $points[$i];
            $total += Haversine::distanceKm((float) $prev->lat, (float) $prev->lng, (float) $curr->lat, (float) $curr->lng);
        }

        return round($total, 2);
    }

    public function durationAtSiteMinutes(): ?int
    {
        if (! $this->arrived_client_at || ! $this->departed_client_at) {
            return null;
        }

        return (int) $this->arrived_client_at->diffInMinutes($this->departed_client_at);
    }

    public function totalTripMinutes(): ?int
    {
        if (! $this->departed_base_at || ! $this->arrived_base_at) {
            return null;
        }

        return (int) $this->departed_base_at->diffInMinutes($this->arrived_base_at);
    }

    public function workerSignatureUrl(): ?string
    {
        return $this->worker_signature_path
            ? route('files.visit-signature', ['visit' => $this, 'who' => 'worker'])
            : null;
    }

    public function secondSignerUrl(): ?string
    {
        return $this->second_signer_path
            ? route('files.visit-signature', ['visit' => $this, 'who' => 'second'])
            : null;
    }
}
