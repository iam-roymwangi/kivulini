<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }

            /* Preloader */
            #app-preloader {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: oklch(1 0 0);
                transition: opacity 0.4s ease, visibility 0.4s ease;
            }

            html.dark #app-preloader {
                background-color: oklch(0.145 0 0);
            }

            #app-preloader.fade-out {
                opacity: 0;
                visibility: hidden;
            }

            .preloader-inner {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1.5rem;
                animation: preloader-fade-in 0.3s ease forwards;
            }

            @keyframes preloader-fade-in {
                from { opacity: 0; transform: translateY(8px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Light logo shown by default, dark logo hidden */
            .preloader-logo-light { display: block; }
            .preloader-logo-dark  { display: none; }

            /* Swap when dark mode is active */
            html.dark .preloader-logo-light { display: none; }
            html.dark .preloader-logo-dark  { display: block; }

            .preloader-logo-light,
            .preloader-logo-dark {
                width: 80px;
                height: 80px;
                object-fit: contain;
            }

            .preloader-dots {
                display: flex;
                gap: 0.4rem;
            }

            .preloader-dots span {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background-color: #4B5563;
                animation: preloader-dot-bounce 1.2s ease-in-out 3;
            }

            html.dark .preloader-dots span {
                background-color: #9CA3AF;
            }

            .preloader-dots span:nth-child(1) { animation-delay: 0s; }
            .preloader-dots span:nth-child(2) { animation-delay: 0.2s; }
            .preloader-dots span:nth-child(3) { animation-delay: 0.4s; }

            @keyframes preloader-dot-bounce {
                0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
                40%            { transform: translateY(-6px); opacity: 1; }
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <meta name="google-site-verification" content="lLCsUFtkLMchpj1b-W6QPRnNaOYpTUDGStmmRcaRa6A" />

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>Kivulini Adventures</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <div id="app-preloader" aria-hidden="true">
            <div class="preloader-inner">
                <img class="preloader-logo-light" src="/assets/images/logo-dark.png" alt="Kivulini Adventures">
                <img class="preloader-logo-dark"  src="/assets/images/kivulini_logo.png"     alt="Kivulini Adventures">
                <div class="preloader-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
        <x-inertia::app />
    </body>
</html>
