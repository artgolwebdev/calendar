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
                --color-success: #16A34A;
                --color-success-bg: #F0FDF4;
                --color-success-border: #DCFCE7;
                
                /* Typography */
                --font-body: 'Heebo', 'Rubik', 'Assistant', system-ui, -apple-system, sans-serif;
                
                /* Type Scale */
                --text-xs: 0.75rem;     /* 12px */
                --text-sm: 0.875rem;    /* 14px */
                --text-base: 0.9375rem; /* 15px */
                --text-lg: 1.125rem;    /* 18px */
                --text-xl: 1.25rem;     /* 20px */
                --text-2xl: 1.625rem;   /* 26px */
                --text-3xl: 2rem;       /* 32px */
                
                /* Spacing Scale */
                --space-1: 0.25rem;  /* 4px */
                --space-2: 0.5rem;   /* 8px */
                --space-3: 0.75rem;  /* 12px */
                --space-4: 1rem;     /* 16px */
                --space-6: 1.5rem;   /* 24px */
                --space-8: 2rem;     /* 32px */
                
                /* Border Radius */
                --radius-sm: 0.375rem; /* 6px */
                --radius-md: 0.5rem;   /* 8px */
                --radius-lg: 0.75rem;  /* 12px */
            }
            
            body {
                font-family: var(--font-body);
                background-color: var(--color-bg);
                color: var(--color-text-primary);
                line-height: 1.5;
                -webkit-font-smoothing: antialiased;
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-family: var(--font-body);
                font-weight: 600;
                line-height: 1.25;
                color: var(--color-text-primary);
                letter-spacing: -0.01em;
            }
            
            h1 { font-size: var(--text-2xl); }
            h2 { font-size: var(--text-xl); }
            h3 { font-size: var(--text-lg); }
            h4 { font-size: var(--text-base); }
            
            .text-display {
                font-family: var(--font-body);
            }
            
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 var(--space-4);
            }
            
            @media (min-width: 640px) {
                .container { padding: 0 var(--space-6); }
            }
            
            @media (min-width: 1024px) {
                .container { padding: 0 var(--space-8); }
            }
            
            /* Card System */
            .card {
                background-color: var(--color-surface);
                border-radius: var(--radius-md);
                border: 1px solid var(--color-border);
                box-shadow: none;
            }
            
            /* Button System */
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                height: 2.5rem; /* 40px */
                padding: 0 1rem;
                border-radius: var(--radius-md);
                font-weight: 500;
                font-size: var(--text-sm);
                line-height: 1;
                transition: all 0.15s ease-in-out;
                cursor: pointer;
                border: 1px solid transparent;
                font-family: var(--font-body);
                text-decoration: none;
                white-space: nowrap;
            }
            
            .btn-sm {
                height: 2rem; /* 32px */
                padding: 0 0.75rem;
                font-size: var(--text-xs);
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
                color: var(--color-text-primary);
            }
            
            .btn-ghost {
                background-color: transparent;
                color: var(--color-accent);
                border-color: transparent;
            }
            
            .btn-ghost:hover {
                background-color: var(--color-accent-light);
                color: var(--color-accent-hover);
            }

            .btn-text-muted {
                background-color: transparent;
                color: var(--color-text-secondary);
                border-color: transparent;
            }

            .btn-text-muted:hover {
                background-color: var(--color-bg);
                color: var(--color-text-primary);
            }
            
            .btn-destructive {
                background-color: #FFFFFF;
                color: var(--color-error);
                border-color: #FCA5A5;
            }
            
            .btn-destructive:hover {
                background-color: var(--color-error-light);
                border-color: var(--color-error);
            }
            
            .btn-destructive-solid {
                background-color: var(--color-error);
                color: #FFFFFF;
                border-color: transparent;
            }
            
            .btn-destructive-solid:hover {
                background-color: var(--color-error-hover);
            }
            
            /* Form Inputs */
            .input {
                width: 100%;
                height: 2.5rem; /* 40px */
                padding: 0 0.75rem;
                border: 1px solid var(--color-border);
                border-radius: var(--radius-md);
                font-size: var(--text-sm);
                font-family: var(--font-body);
                background-color: #FFFFFF;
                color: var(--color-text-primary);
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }

            textarea.input {
                height: auto;
                padding: 0.5rem 0.75rem;
            }
            
            .input:focus {
                outline: none;
                border-color: var(--color-accent);
                box-shadow: 0 0 0 2px var(--color-accent-ring);
            }
            
            .label {
                display: block;
                font-size: var(--text-sm);
                font-weight: 500;
                color: var(--color-text-secondary);
                margin-bottom: 0.375rem;
            }
            
            /* Badges / Chips */
            .chip {
                display: inline-flex;
                align-items: center;
                padding: 0.125rem 0.5rem;
                border-radius: var(--radius-sm);
                font-size: var(--text-xs);
                font-weight: 500;
                line-height: 1.4;
            }
            
            .chip-holiday {
                background-color: #DCFCE7;
                color: #15803D;
            }
            
            .chip-event {
                background-color: #FFEDD5;
                color: #C2410C;
            }
            
            .chip-birthday {
                background-color: #F3E8FF;
                color: #6B21A8;
            }
            
            .chip-anniversary {
                background-color: #E0F2FE;
                color: #0369A1;
            }

            [x-cloak] {
                display: none;
            }

            /* Collapsible desktop sidebar */
            .side-active-dot {
                display: none;
            }

            .sidebar-ready .side-pad {
                transition: padding 300ms ease-in-out;
            }

            .sidebar-ready .side-aside {
                transition: width 300ms ease-in-out;
            }

            @media (min-width: 1024px) {
                .side-pad.side-pad {
                    padding-inline-start: 18rem;
                }

                .side-aside {
                    width: 18rem;
                }

                .app-sidebar-collapsed .side-pad {
                    padding-inline-start: 5rem;
                }

                .app-sidebar-collapsed .side-aside {
                    width: 5rem;
                }

                .app-sidebar-collapsed .side-label,
                .app-sidebar-collapsed .side-user-text,
                .app-sidebar-collapsed .side-chevron,
                .app-sidebar-collapsed .side-submenu,
                .app-sidebar-collapsed .side-active-bar {
                    display: none;
                }

                .app-sidebar-collapsed .side-item,
                .app-sidebar-collapsed .side-user {
                    justify-content: center;
                }

                .app-sidebar-collapsed .side-active-dot {
                    display: flex;
                }
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen" x-data="{ ready: false }" x-init="requestAnimationFrame(() => ready = true)"
             :class="(ready ? 'sidebar-ready ' : '') + ($store.sidebar.collapsed ? 'app-sidebar-collapsed' : '')">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-[var(--color-border)]">
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
