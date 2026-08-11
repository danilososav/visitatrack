<div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 mb-6">
    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">🧪 Entorno de demostración</p>
    <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">
        Proyecto de portafolio, sin datos reales. Elegí un usuario de prueba para autocompletar el acceso.
    </p>

    <div class="mt-3 flex flex-col gap-2">
        <button type="button"
            x-on:click="$wire.set('data.email', 'admin@visitatrack.test'); $wire.set('data.password', 'password')"
            class="w-full text-left rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-white/5 hover:bg-amber-100 dark:hover:bg-white/10 transition-colors px-3 py-2">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">👑 Administrador general</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">Acceso total al panel</span>
        </button>
    </div>

    <p class="text-xs text-amber-700 dark:text-amber-400 mt-3">
        ¿Sos trabajador de campo? <a href="/portal/login" class="underline font-medium">Ingresá al portal del trabajador</a>.
    </p>
</div>
