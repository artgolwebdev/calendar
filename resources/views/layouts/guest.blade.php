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
        <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@300;400;500;600;700&family=Heebo:wght@300;400;500;600;700&family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Design system is centralized in resources/css/app.css -->
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 bg-ink-50">
            <div class="mb-6">
                <a href="/" class="flex items-center gap-2.5 text-xl font-bold text-ink-900 hover:opacity-90 transition-opacity">
                    <span class="w-8 h-8 rounded-lg bg-ink-950 flex items-center justify-center text-volt text-sm font-bold">ל</span>
                    <span>לוח שנה משפחתי</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md card p-6 rounded-xl shadow-none">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
