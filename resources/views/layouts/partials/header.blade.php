<header class="topbar-mdm d-flex align-items-center justify-content-between px-3 px-md-4">
    <!-- Left: Hamburger Toggle (Mobile) / Desktop Mini Sidebar Toggle + Page Title & Breadcrumb -->
    <div class="d-flex align-items-center gap-2 gap-md-3 overflow-hidden">
        <!-- Hamburger Menu Button visible only on < lg (Mobile Offcanvas) -->
        <button class="btn btn-outline-secondary btn-sm d-lg-none d-flex align-items-center justify-content-center p-2 rounded-2 flex-shrink-0" 
                type="button" 
                data-bs-toggle="offcanvas" 
                data-bs-target="#sidebarMdm" 
                aria-controls="sidebarMdm"
                aria-label="Toggle navigation">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Desktop Mini Sidebar Toggle (Icon-only mode on desktop) -->
        <button id="desktopSidebarToggle" 
                class="btn btn-outline-secondary btn-sm d-none d-lg-inline-flex align-items-center justify-content-center p-2 rounded-2 flex-shrink-0" 
                type="button" 
                title="Ciutkan / Lebarkan Sidebar"
                aria-label="Toggle Mini Sidebar">
            <i class="bi bi-list fs-5"></i>
        </button>

        <h4 class="mb-0 fw-bold fs-5 text-dark text-truncate">@yield('page_title', 'Dashboard Overview')</h4>
        <div class="vr text-secondary d-none d-md-block" style="height: 20px;"></div>
        <span class="badge bg-light text-secondary border font-mono small d-none d-md-inline-flex align-items-center gap-1">
            <i class="bi bi-building"></i>
            {{ config('amapi.enterprise_id') }}
        </span>
    </div>

    <!-- Right: Quick Actions -->
    <div class="d-flex align-items-center gap-2 flex-shrink-0">
        @if(auth()->check() && auth()->user()->hasPermission('devices.sync'))
        <form action="{{ route('devices.sync') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Tarik data terbaru dari Google AMAPI">
                <i class="bi bi-arrow-repeat"></i>
                <span class="d-none d-sm-inline">Sync AMAPI</span>
            </button>
        </form>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission('enrollment.manage'))
        <a href="{{ route('enrollment.index') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-plus-circle"></i>
            <span class="d-none d-sm-inline">Enroll Device</span>
            <span class="d-sm-none">Enroll</span>
        </a>
        @endif
    </div>
</header>
