<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'KanAI') }}</title>
        <script>
            (() => {
                const saved = localStorage.getItem('kanai-theme') ?? 'auto';
                const dark = saved === 'dark' || (saved === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.setAttribute('data-theme', saved);
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased bg-gradient-to-br from-slate-50 via-indigo-50 to-sky-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 text-slate-900 dark:text-slate-100">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                    <div class="glass-card px-5 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-10">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
