<header class="topbar-mdm d-flex align-items-center justify-content-between px-4">
    <!-- Left: Page Title & Breadcrumb -->
    <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0 fw-bold fs-5 text-dark">@yield('page_title', 'Dashboard Overview')</h4>
        <div class="vr text-secondary d-none d-md-block" style="height: 20px;"></div>
        <span class="badge bg-light text-secondary border font-mono small d-none d-md-inline-flex align-items-center gap-1">
            <i class="bi bi-building"></i>
            {{ config('amapi.enterprise_id') }}
        </span>
    </div>

    <!-- Right: Quick Actions -->
    <div class="d-flex align-items-center gap-2">
        <form action="{{ route('devices.sync') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Tarik data terbaru dari Google AMAPI">
                <i class="bi bi-arrow-repeat"></i>
                <span class="d-none d-sm-inline">Sync AMAPI</span>
            </button>
        </form>

        <a href="{{ route('enrollment.index') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-plus-circle"></i>
            <span>Enroll Device</span>
        </a>
    </div>
</header>
