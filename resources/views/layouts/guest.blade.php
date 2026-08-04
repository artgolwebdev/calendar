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

        <!-- Design System Styles (Minimalistic CRM Theme) -->
        <style>
            :root {
                /* Neutral CRM Color System */
                --color-bg: #F7F7F8;
                --color-surface: #FFFFFF;
                --color-white: #FFFFFF;
                --color-border: #E5E5E8;
                --color-border-hover: #D4D4D8;
                --color-text-primary: #1A1A1E;
                --color-text-secondary: #6B6B75;
                
                /* Accent Color (Indigo) */
                --color-accent: #4F46E5;
                --color-accent-hover: #4338CA;
                --color-accent-light: #EEF2FF;
                --color-accent-ring: rgba(79, 70, 229, 0.25);
                
                /* Status Colors */
                --color-error: #DC2626;
                --color-error-hover: #B91C1C;
                --color-error-light: #FEF2F2;
                
                /* Typography */
                --font-body: 'Heebo', 'Rubik', 'Assistant', system-ui, -apple-system, sans-serif;
                
                --radius-md: 0.5rem; /* 8px */
            }
            
            body {
                font-family: var(--font-body);
                background-color: var(--color-bg);
                color: var(--color-text-primary);
                line-height: 1.5;
                -webkit-font-smoothing: antialiased;
            }
            
            .card {
                background-color: var(--color-surface);
                border-radius: var(--radius-md);
                border: 1px solid var(--color-border);
                box-shadow: none;
            }
            
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                height: 2.5rem; /* 40px */
                padding: 0 1rem;
                border-radius: var(--radius-md);
                font-weight: 500;
                font-size: 0.875rem;
                line-height: 1;
                transition: all 0.15s ease-in-out;
                cursor: pointer;
                border: 1px solid transparent;
                font-family: var(--font-body);
                text-decoration: none;
                white-space: nowrap;
            }
            
            .btn-primary {
                background-color: var(--color-accent);
                color: #FFFFFF;
                border-color: transparent;
            }
            
            .btn-primary:hover {
                background-color: var(--color-accent-hover);
                color: #FFFFFF;
            }
            
            .btn-secondary {
                background-color: #FFFFFF;
                color: var(--color-text-primary);
                border-color: var(--color-border);
            }
            
            .btn-secondary:hover {
                background-color: var(--color-bg);
                border-color: var(--color-border-hover);
            }
            
            .input {
                width: 100%;
                height: 2.5rem; /* 40px */
                padding: 0 0.75rem;
                border: 1px solid var(--color-border);
                border-radius: var(--radius-md);
                font-size: 0.875rem;
                font-family: var(--font-body);
                background-color: #FFFFFF;
                color: var(--color-text-primary);
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            
            .input:focus {
                outline: none;
                border-color: var(--color-accent);
                box-shadow: 0 0 0 2px var(--color-accent-ring);
            }
            
            .label {
                display: block;
                font-size: 0.875rem;
                font-weight: 500;
                color: var(--color-text-secondary);
                margin-bottom: 0.375rem;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 bg-[#F7F7F8]">
            <div class="mb-6">
                <a href="/" class="flex items-center gap-2.5 text-xl font-bold text-[#1A1A1E] hover:opacity-90 transition-opacity">
                    <span class="w-8 h-8 rounded-lg bg-[#4F46E5] flex items-center justify-center text-white text-sm font-bold">ל</span>
                    <span>לוח שנה משפחתי</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md card p-6 bg-white border border-[#E5E5E8] rounded-xl shadow-none">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
