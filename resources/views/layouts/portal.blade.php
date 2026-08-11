<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    {{-- Applied before paint to avoid a light-mode flash on load --}}
    <script>
        (function () {
            var stored = localStorage.getItem('vt-theme');
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/signature_pad@5.0.4/dist/signature_pad.umd.min.js" defer></script>
</head>
<body class="h-full bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-slate-100 antialiased transition-colors">
    <button
        type="button"
        x-data="{ dark: document.documentElement.classList.contains('dark') }"
        x-on:click="
            dark = !dark;
            document.documentElement.classList.toggle('dark', dark);
            localStorage.setItem('vt-theme', dark ? 'dark' : 'light');
        "
        class="fixed top-3 right-3 z-50 w-10 h-10 rounded-full bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-sm flex items-center justify-center text-base"
        aria-label="Cambiar tema"
    >
        <span x-show="!dark">🌙</span>
        <span x-show="dark" x-cloak>☀️</span>
    </button>

    {{ $slot }}

    @livewireScripts

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
        }
    </script>
</body>
</html>
