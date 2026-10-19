<div class="sidebar-mdm offcanvas-lg offcanvas-start d-flex flex-column p-3 text-white flex-shrink-0" tabindex="-1" id="sidebarMdm" aria-labelledby="sidebarMdmLabel">
    <!-- Brand Header & Mobile Close Button -->
    <div class="d-flex align-items-center justify-content-between mb-4 px-2">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo-amanin-transparent.png') }}" alt="Amanin - Device Management Platform" class="img-fluid" style="max-height: 44px; width: auto; object-fit: contain;">
        </a>
        <button type="button" class="btn btn-link text-white-50 p-1 d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMdm" aria-label="Tutup Menu">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.*') ? 'active' : '' }}">
                <i class="bi bi-phone"></i>
                <span>Devices</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('policies.index') }}" class="nav-link {{ request()->routeIs('policies.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i>
                <span>Policies</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('apps.index') }}" class="nav-link {{ request()->routeIs('apps.*') ? 'active' : '' }}">
                <i class="bi bi-google-play"></i>
                <span>Google Play Apps</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('enrollment.index') }}" class="nav-link {{ request()->routeIs('enrollment.*') ? 'active' : '' }}">
                <i class="bi bi-qr-code-scan"></i>
                <span>Enrollment QR</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Audit Logs</span>
            </a>
        </li>

        @if(auth()->check() && auth()->user()->isAdmin())
        <li class="nav-heading text-uppercase text-secondary small fw-bold px-3 mt-4 mb-2" style="font-size: 11px; letter-spacing: 0.5px; color: #64748B;">
            Administrator
        </li>
        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Manage Users</span>
            </a>
        </li>
        @endif
    </ul>

    <!-- User Profile Footer -->
    <div class="border-top border-secondary pt-3 mt-3 px-2 d-flex align-items-center justify-content-between" style="border-color: rgba(255,255,255,0.1) !important;">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 34px; height: 34px; font-size: 13px;">
                {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username ?? 'A', 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <div class="fw-semibold text-truncate small">{{ auth()->user()->name ?? auth()->user()->username ?? 'Administrator' }}</div>
                <div class="text-muted" style="font-size: 11px;">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-link text-secondary p-1 text-decoration-none" title="Logout">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </button>
        </form>
    </div>
</div>
