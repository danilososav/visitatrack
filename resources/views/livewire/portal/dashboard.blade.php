<div class="min-h-screen bg-gray-50 flex flex-col">
    <header class="flex items-center justify-between px-5 py-4 bg-white border-b border-gray-100">
        <div>
            <p class="text-xs text-gray-400">Hola,</p>
            <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
        </div>
        <form action="{{ route('portal.logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Salir</button>
        </form>
    </header>

    <main class="flex-1 px-5 py-6 flex flex-col gap-6 max-w-lg w-full mx-auto">
        @if ($activeVisit)
            <a href="{{ route('portal.visits.show', $activeVisit) }}" wire:navigate
                class="block rounded-2xl bg-blue-600 text-white p-5 shadow-lg shadow-blue-600/20">
                <p class="text-xs uppercase tracking-wide text-blue-200">Visita en curso</p>
                <p class="text-lg font-bold mt-1">
                    {{ $activeVisit->company?->name ?? $activeVisit->machine?->name ?? 'Continuar' }}
                </p>
                <p class="text-sm text-blue-100 mt-1">Toca para continuar &rarr;</p>
            </a>
        @else
            <a href="{{ route('portal.visits.create') }}" wire:navigate
                class="block text-center rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white py-5 shadow-lg shadow-blue-600/30 font-bold text-lg">
                + Iniciar nueva visita
            </a>
        @endif

        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Historial</h2>

            @if ($recentVisits->isEmpty())
                <p class="text-sm text-gray-400">Todavía no tenés visitas registradas.</p>
            @else
                <div class="flex flex-col gap-2">
                    @foreach ($recentVisits as $visit)
                        <div class="rounded-xl bg-white border border-gray-100 p-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 text-sm">
                                    {{ $visit->company?->name ?? $visit->machine?->name ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $visit->departed_base_at?->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <span @class([
                                'text-xs font-medium px-2.5 py-1 rounded-full',
                                'bg-green-100 text-green-700' => $visit->status === 'completed',
                                'bg-amber-100 text-amber-700' => $visit->status === 'pending_approval',
                                'bg-red-100 text-red-700' => $visit->status === 'cancelled',
                            ])>
                                {{ match($visit->status) {
                                    'completed' => 'Completada',
                                    'pending_approval' => 'Pendiente',
                                    'cancelled' => 'Cancelada',
                                    default => $visit->status,
                                } }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</div>
