<div class="sidebar-mdm offcanvas-lg offcanvas-start d-flex flex-column p-3 text-white flex-shrink-0" tabindex="-1" id="sidebarMdm" aria-labelledby="sidebarMdmLabel">
    <!-- Brand Header & Mobile Close Button -->
    <div class="brand-header d-flex align-items-center justify-content-between mb-4 px-2">
        <a href="{{ route('dashboard') }}" class="brand-link d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo-amanin-transparent.png') }}" alt="Amanin - Device Management Platform" class="brand-full img-fluid" style="max-height: 44px; width: auto; object-fit: contain;">
            <img src="{{ asset('images/logo-icon.png') }}" alt="Amanin" class="brand-mini img-fluid d-none" style="max-height: 36px; width: auto; object-fit: contain;">
        </a>
        <button type="button" class="btn btn-link text-white-50 p-1 d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMdm" aria-label="Tutup Menu">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        @if(auth()->check() && auth()->user()->hasPermission('devices.view'))
        <li class="nav-item">
            <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.*') && !request()->routeIs('devices.offline-alerts') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Devices">
                <i class="bi bi-phone"></i>
                <span>Devices</span>
            </a>
        </li>
        <li class="nav-item">
            @php
                $globalOfflineCount = \App\Models\Device::forUser()->offlineAlert()->count();
            @endphp
            <a href="{{ route('devices.offline-alerts') }}" 
               class="nav-link {{ request()->routeIs('devices.offline-alerts') ? 'active' : '' }}" 
               data-bs-toggle="tooltip" data-bs-placement="right" title="Peringatan Offline">
                <i class="bi bi-exclamation-triangle"></i>
                <span class="d-flex align-items-center justify-content-between flex-grow-1">
                    <span>Peringatan Offline</span>
                    @if($globalOfflineCount > 0)
                        <span class="badge rounded-pill font-mono shadow-sm text-white" 
                              style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); font-size: 10px; padding: 3px 7px;">
                            {{ $globalOfflineCount }}
                        </span>
                    @endif
                </span>
            </a>
        </li>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission('policies.view'))
        <li class="nav-item">
            <a href="{{ route('policies.index') }}" class="nav-link {{ request()->routeIs('policies.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Policies">
                <i class="bi bi-shield-lock"></i>
                <span>Policies</span>
            </a>
        </li>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission('apps.view'))
        <li class="nav-item">
            <a href="{{ route('apps.index') }}" class="nav-link {{ request()->routeIs('apps.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Google Play Apps">
                <i class="bi bi-google-play"></i>
                <span>Google Play Apps</span>
            </a>
        </li>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission('enrollment.manage'))
        <li class="nav-item">
            <a href="{{ route('enrollment.index') }}" class="nav-link {{ request()->routeIs('enrollment.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Enrollment QR">
                <i class="bi bi-qr-code-scan"></i>
                <span>Enrollment QR</span>
            </a>
        </li>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission('audit_logs.view'))
        <li class="nav-item">
            <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Audit Logs">
                <i class="bi bi-journal-text"></i>
                <span>Audit Logs</span>
            </a>
        </li>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission(['branches.manage', 'users.manage']))
        <li class="nav-heading text-uppercase text-secondary small fw-bold px-3 mt-4 mb-2" style="font-size: 11px; letter-spacing: 0.5px; color: #64748B;">
            Administrator
        </li>
        <li class="admin-separator d-none" style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin: 0.75rem 0.5rem;"></li>

        @if(auth()->user()->hasPermission('branches.manage'))
        <li class="nav-item">
            <a href="{{ route('branches.index') }}" class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Master Cabang">
                <i class="bi bi-building"></i>
                <span>Master Cabang</span>
            </a>
        </li>
        @endif

        @if(auth()->user()->hasPermission('users.manage'))
        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage Users">
                <i class="bi bi-people"></i>
                <span>Manage Users</span>
            </a>
        </li>
        @endif

        @if(auth()->user()->isAdmin())
        <li class="nav-item">
            <a href="{{ route('enterprises.index') }}" class="nav-link {{ request()->routeIs('enterprises.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Kelola Enterprise">
                <i class="bi bi-briefcase"></i>
                <span>Kelola Enterprise</span>
            </a>
        </li>
        @endif
        @endif
    </ul>

    <!-- User Profile Footer -->
    <div class="user-profile-section border-top border-secondary pt-3 mt-3 px-2 d-flex align-items-center justify-content-between" style="border-color: rgba(255,255,255,0.1) !important;">
        <div class="user-info d-flex align-items-center gap-2 overflow-hidden">
            <div class="user-avatar rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 34px; height: 34px; font-size: 13px;" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ auth()->user()->name ?? auth()->user()->username ?? 'Administrator' }} ({{ ucfirst(auth()->user()->role ?? 'Admin') }}{{ auth()->user()?->branch ? ' • ' . auth()->user()->branch->code : ' • Pusat' }})">
                {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username ?? 'A', 0, 1)) }}
            </div>
            <div class="user-details overflow-hidden">
                <div class="fw-semibold text-truncate small">{{ auth()->user()->name ?? auth()->user()->username ?? 'Administrator' }}</div>
                <div class="text-muted text-truncate" style="font-size: 11px;">
                    {{ ucfirst(auth()->user()->role ?? 'Admin') }} • {{ auth()->user()?->branch ? auth()->user()->branch->code : 'Pusat' }}
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="logout-form m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-link text-secondary p-1 text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="right" title="Logout">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </button>
        </form>
    </div>
</div>
