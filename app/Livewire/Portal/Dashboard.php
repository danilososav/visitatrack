<?php

namespace App\Livewire\Portal;

use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.portal')]
class Dashboard extends Component
{
    public function getActiveVisitProperty(): ?Visit
    {
        return Visit::query()
            ->where('worker_id', Auth::id())
            ->whereIn('status', Visit::ACTIVE_STATUSES)
            ->latest('created_at')
            ->first();
    }

    public function getRecentVisitsProperty()
    {
        return Visit::query()
            ->where('worker_id', Auth::id())
            ->whereNotIn('status', Visit::ACTIVE_STATUSES)
            ->latest('created_at')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.portal.dashboard', [
            'activeVisit' => $this->activeVisit,
            'recentVisits' => $this->recentVisits,
        ]);
    }
}
