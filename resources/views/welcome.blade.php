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
        <link href="https://fonts.googleapis.com/css2?family=Frank+Ruhl+Libre:wght@400;500;700&family=Heebo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Design System Styles -->
        <style>
            :root {
                /* Colors */
                --color-bg: #FAF8F3;
                --color-text-primary: #2B2E3A;
                --color-text-secondary: #8A8578;
                --color-accent: #3F6B4F;
                --color-accent-hover: #355A42;
                --color-white: #FFFFFF;
                --color-border: #E8E6E1;
                
                /* Typography */
                --font-display: 'Frank Ruhl Libre', serif;
                --font-body: 'Heebo', sans-serif;
                
                /* Type Scale */
                --text-xs: 0.8125rem;
                --text-sm: 0.875rem;
                --text-base: 1rem;
                --text-lg: 1.125rem;
                --text-xl: 1.25rem;
                --text-2xl: 1.5rem;
                --text-3xl: 2rem;
                --text-4xl: 2.5rem;
                
                /* Spacing Scale */
                --space-1: 0.25rem;
                --space-2: 0.5rem;
                --space-3: 0.75rem;
                --space-4: 1rem;
                --space-6: 1.5rem;
                --space-8: 2rem;
                --space-12: 3rem;
                
                /* Border Radius */
                --radius-sm: 0.375rem;
                --radius-md: 0.5rem;
                --radius-lg: 0.75rem;
                
                /* Shadows */
                --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            
            body {
                font-family: var(--font-body);
                background-color: var(--color-bg);
                color: var(--color-text-primary);
                line-height: 1.7;
                letter-spacing: 0.01em;
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-family: var(--font-display);
                font-weight: 500;
                line-height: 1.3;
                color: var(--color-text-primary);
            }
            
            .text-display { font-family: var(--font-display); }
            .text-body { font-family: var(--font-body); }
            
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
            
            .card {
                background-color: var(--color-white);
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-md);
                border: 1px solid var(--color-border);
            }
            
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: var(--space-3) var(--space-6);
                border-radius: var(--radius-md);
                font-weight: 500;
                font-size: var(--text-sm);
                transition: all 0.2s ease;
                cursor: pointer;
                border: none;
                font-family: var(--font-body);
            }
            
            .btn-primary {
                background-color: var(--color-accent);
                color: var(--color-white);
            }
            
            .btn-primary:hover {
                background-color: var(--color-accent-hover);
            }
            
            .btn-secondary {
                background-color: var(--color-white);
                color: var(--color-text-primary);
                border: 1px solid var(--color-border);
            }
            
            .btn-secondary:hover {
                background-color: var(--color-bg);
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col">
            <!-- Navigation -->
            <nav class="bg-white border-b" style="border-color: var(--color-border);">
                <div class="container">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <a href="/" class="text-display text-xl font-bold" style="color: var(--color-accent);">
                                לוח שנה משפחתי
                            </a>
                        </div>
                        <div class="flex items-center space-x-4 space-x-reverse">
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-sm font-medium transition-colors" style="color: var(--color-text-secondary); hover: color: var(--color-text-primary);">
                                    לוח בקרה
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium transition-colors" style="color: var(--color-text-secondary); hover: color: var(--color-text-primary);">
                                    התחברות
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-primary">
                                    הרשמה
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <main class="flex-grow flex items-center justify-center py-12">
                <div class="container text-center">
                    <h1 class="text-display text-4xl sm:text-5xl font-bold mb-6" style="color: var(--color-text-primary);">
                        לוח שנה משפחתי בעברית
                    </h1>
                    <p class="text-body text-lg mb-8 max-w-2xl mx-auto" style="color: var(--color-text-secondary); line-height: 1.8;">
                        נהל את לוח השנה המשפחתי שלך, עקוב אחר ימי הולדת, ימי נישואין וחגים ישראליים - הכל במקום אחד
                    </p>
                    @guest
                        <div class="flex justify-center space-x-4 space-x-reverse">
                            <a href="{{ route('register') }}" class="btn btn-primary" style="padding: var(--space-3) var(--space-8); font-size: var(--text-base);">
                                צור חשבון
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-secondary" style="padding: var(--space-3) var(--space-8); font-size: var(--text-base);">
                                התחברות
                            </a>
                        </div>
                    @endguest
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t py-6" style="border-color: var(--color-border);">
                <div class="container">
                    <p class="text-center text-sm" style="color: var(--color-text-secondary);">
                        © {{ date('Y') }} לוח שנה משפחתי. כל הזכויות שמורות.
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>