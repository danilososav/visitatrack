<div class="min-h-screen flex flex-col items-center justify-center px-6 gap-8">
    <div class="flex flex-col items-center gap-3">
        <img src="/icons/icon-192.png" alt="VisitaTrack" class="w-16 h-16 rounded-2xl shadow">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">VisitaTrack</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Portal del trabajador</p>
        </div>
    </div>

    <div class="w-full max-w-sm rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 px-4 py-3">
        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">🧪 Entorno de demostración</p>
        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">
            Proyecto de portafolio, sin datos reales. Elegí un usuario de prueba para entrar directo, según su rol.
        </p>

        <div class="mt-3 flex flex-col gap-2">
            @foreach ($demoUsers as $demo)
                <button type="button"
                    wire:click="loginAsDemo('{{ $demo['email'] }}', '{{ $demo['password'] }}')"
                    wire:loading.attr="disabled"
                    class="w-full text-left rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 hover:bg-amber-100 dark:hover:bg-slate-700 transition-colors px-3 py-2 disabled:opacity-50">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $demo['label'] }}</span>
                    <span class="block text-xs text-gray-500 dark:text-slate-400">{{ $demo['description'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <form wire:submit="authenticate" class="w-full max-w-sm flex flex-col gap-4">
        @if ($loginError)
            <div class="rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
                {{ $loginError }}
            </div>
        @endif

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Email</label>
            <input type="email" wire:model="email" required autofocus
                class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            @error('email') <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Contraseña</label>
            <input type="password" wire:model="password" required
                class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            @error('password') <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
            <input type="checkbox" wire:model="remember" class="rounded border-gray-300 dark:border-slate-600 dark:bg-slate-800">
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
