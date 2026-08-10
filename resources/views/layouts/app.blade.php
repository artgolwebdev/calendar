<!DOCTYPE html>
<html lang="he" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'לוח שנה משפחתי') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@300;400;500;600;700&family=Frank+Ruhl+Libre:wght@400;500;700&family=Heebo:wght@300;400;500;600;700&family=Rubik:wght@300;400;500;600;700&family=Secular+One:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Design system is centralized in resources/css/app.css -->
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen" x-data="{ ready: false }" x-init="requestAnimationFrame(() => ready = true)"
             :class="(ready ? 'sidebar-ready ' : '') + ($store.sidebar.collapsed ? 'app-sidebar-collapsed' : '')">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-[var(--color-border)] side-pad">
                    <div class="container py-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="side-pad">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
