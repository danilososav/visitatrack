<div class="min-h-screen flex flex-col items-center justify-center px-6 gap-8">
    <div class="flex flex-col items-center gap-3">
        <img src="/icons/icon-192.png" alt="VisitaTrack" class="w-16 h-16 rounded-2xl shadow">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">VisitaTrack</h1>
            <p class="text-sm text-gray-500 mt-1">Portal del trabajador</p>
        </div>
    </div>

    <form wire:submit="authenticate" class="w-full max-w-sm flex flex-col gap-4">
        @if ($loginError)
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                {{ $loginError }}
            </div>
        @endif

        <div>
            <label class="text-sm font-medium text-gray-700">Email</label>
            <input type="email" wire:model="email" required autofocus
                class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">Contraseña</label>
            <input type="password" wire:model="password" required
                class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" wire:model="remember" class="rounded border-gray-300">
            Recordarme
        </label>

        <button type="submit"
            class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-semibold shadow-lg shadow-blue-600/30"
            wire:loading.attr="disabled" wire:target="authenticate">
            <span wire:loading.remove wire:target="authenticate">Ingresar</span>
            <span wire:loading wire:target="authenticate">Ingresando...</span>
        </button>
    </form>
</div>
