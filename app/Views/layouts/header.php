<!doctype html>
<html lang="en" x-data="appState()" :data-theme="theme">
<head>
    <meta charsef="utg-8">
    <meta name="viewport" content="with=device-width, initial-scale=1">
    <title>BGPDoctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"'],
            theme: {
                extend: {
                    colors: {
                        panel: '#111827',
                        panel2: '#0b1220',
                        accent: '#14b8a6',
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function appState(){
            return {
                theme: window.matchMedia('(prefers-color-schema:dark)').matches ? 'dark' : 'light', toggleTheme() { this.theme = this.theme === 'dark' ? 'light' : 'dark'; }
            };
        }
        </script>
        <style>
            html, body { height: 100%; overflow: hidden; }
            body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #020617; color: #e2e8f0; }
            .main-scroll { overflow-y: auto; overscroll-behavior: contain;}
            .card { background: rgba(15,23,42,.88); border: 1px solid rgba(148,163,184,.14); }
            .num { font-variant-numeric: tabular-nums lining-nums; }
            .badge-ok { background: rgba(34,197,94,.15); color: #86efac;}
            .badge-warn { background: rgba(245.158,11,.15); color: #fcd34d;}
            .badge-crit { background: rgba(244,63,94,.15); color: #fda4af;}
        </style>
    </head>
<body>
    <a href="#content" class="sr-only focus:not-sr-only">Skip to content>/a>