<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Amanin - Device Management Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Google Fonts: Inter, Plus Jakarta Sans, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- MDM Enterprise Tosca Theme -->
    <link rel="stylesheet" href="{{ asset('css/theme-tosca.css') }}?v={{ filemtime(public_path('css/theme-tosca.css')) }}">

    @stack('styles')
</head>
<body>
    <!-- Instant restoration of mini sidebar state to prevent visual flicker -->
    <script>
        (function() {
            try {
                if (localStorage.getItem('mdm_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
                    document.body.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <div class="d-flex">
        <!-- Sidebar Navigation -->
        @include('layouts.partials.sidebar')

        <!-- Main Wrapper -->
        <div class="d-flex flex-column flex-grow-1 w-100 overflow-hidden" style="min-height: 100vh; min-width: 0;">
            <!-- Topbar Header -->
            @include('layouts.partials.header')

            <!-- Main Page Content -->
            <main class="flex-grow-1 p-3 p-md-4 overflow-y-auto">
                @include('layouts.partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Sidebar Mini & Tooltip Controller -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize tooltips for sidebar
            const sidebarTooltips = [];
            const tooltipTriggerList = document.querySelectorAll('.sidebar-mdm [data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(function (el) {
                sidebarTooltips.push(new bootstrap.Tooltip(el, {
                    customClass: 'sidebar-tooltip',
                    trigger: 'hover',
                    fallbackPlacements: ['bottom']
                }));
            });

            // Only show sidebar tooltips when sidebar is collapsed on desktop
            document.addEventListener('show.bs.tooltip', function (e) {
                if (e.target.closest('.sidebar-mdm')) {
                    if (!document.body.classList.contains('sidebar-collapsed') || window.innerWidth < 992) {
                        e.preventDefault();
                    }
                }
            });

            // Toggle mini sidebar on desktop
            const desktopToggle = document.getElementById('desktopSidebarToggle');
            if (desktopToggle) {
                desktopToggle.addEventListener('click', function () {
                    document.body.classList.toggle('sidebar-collapsed');
                    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                    try {
                        localStorage.setItem('mdm_sidebar_collapsed', isCollapsed ? 'true' : 'false');
                    } catch (e) {}

                    // Hide any active tooltips immediately upon toggle
                    sidebarTooltips.forEach(function (t) {
                        t.hide();
                    });
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
