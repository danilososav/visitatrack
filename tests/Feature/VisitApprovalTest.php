<?php

namespace Tests\Feature;

use App\Filament\Resources\Visits\Pages\ListVisits;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisitApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_visit(): void
    {
        $admin = User::factory()->admin()->create();
        $visit = Visit::factory()->pendingApproval()->create([
            'worker_id' => User::factory()->worker()->create()->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListVisits::class)
            ->callTableAction(['review', 'approve'], record: $visit);

        $visit->refresh();

        $this->assertSame(Visit::STATUS_COMPLETED, $visit->status);
        $this->assertSame($admin->id, $visit->approved_by);
        $this->assertNotNull($visit->approved_at);
    }

    public function test_admin_can_cancel_a_pending_visit(): void
    {
        $admin = User::factory()->admin()->create();
        $visit = Visit::factory()->pendingApproval()->create([
            'worker_id' => User::factory()->worker()->create()->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListVisits::class)
            ->callTableAction(['review', 'cancel'], record: $visit);

        $visit->refresh();

        $this->assertSame(Visit::STATUS_CANCELLED, $visit->status);
    }

    public function test_worker_cannot_access_the_admin_panel(): void
    {
        $worker = User::factory()->worker()->create();

        $this->assertFalse($worker->canAccessPanel(filament()->getPanel('admin')));
    }
}
