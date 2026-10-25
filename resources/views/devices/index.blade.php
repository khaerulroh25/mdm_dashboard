@extends('layouts.app')

@section('title', 'Daftar Perangkat')
@section('page_title', 'Manajemen Perangkat')

@section('content')
<!-- Navigation Tabs: Perangkat Aktif vs Lunas vs Riwayat Unenroll (Admin Only) -->
<ul class="nav nav-pills gap-2 mb-4 bg-white p-2 rounded-3 shadow-sm border">
    <li class="nav-item">
        <a class="nav-link {{ $currentTab === 'active' ? 'active bg-teal' : 'text-dark' }} fw-semibold d-flex align-items-center gap-2 py-2 px-3"
           href="{{ route('devices.index', array_merge(request()->except('tab', 'page'), ['tab' => 'active'])) }}">
            <i class="bi bi-phone-fill"></i>
            <span>Perangkat Aktif</span>
            <span class="badge {{ $currentTab === 'active' ? 'bg-white text-teal' : 'bg-light text-secondary border' }} rounded-pill ms-1">
                {{ $activeCount }}
            </span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $currentTab === 'lunas' ? 'active bg-success text-white' : 'text-dark' }} fw-semibold d-flex align-items-center gap-2 py-2 px-3"
           href="{{ route('devices.index', array_merge(request()->except('tab', 'page'), ['tab' => 'lunas'])) }}">
            <i class="bi bi-patch-check-fill {{ $currentTab === 'lunas' ? 'text-white' : 'text-success' }}"></i>
            <span>Lunas</span>
            <span class="badge {{ $currentTab === 'lunas' ? 'bg-white text-success' : 'bg-light text-secondary border' }} rounded-pill ms-1">
                {{ $lunasCount }}
            </span>
        </a>
    </li>
    @if(auth()->user()->isAdmin())
    <li class="nav-item">
        <a class="nav-link {{ $currentTab === 'deleted' ? 'active bg-secondary' : 'text-dark' }} fw-semibold d-flex align-items-center gap-2 py-2 px-3"
           href="{{ route('devices.index', array_merge(request()->except('tab', 'page'), ['tab' => 'deleted'])) }}">
            <i class="bi bi-archive-fill"></i>
            <span>Riwayat Unenroll</span>
            <span class="badge {{ $currentTab === 'deleted' ? 'bg-white text-dark' : 'bg-light text-secondary border' }} rounded-pill ms-1">
                {{ $deletedCount }}
            </span>
        </a>
    </li>
    @endif
    <li class="nav-item ms-auto">
        <a class="nav-link {{ $currentTab === 'all' ? 'active bg-dark text-white' : 'text-secondary' }} small d-flex align-items-center gap-1 py-2 px-3"
           href="{{ route('devices.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}">
            <i class="bi bi-list-ul"></i>
            <span>Semua ({{ auth()->user()->isAdmin() ? ($activeCount + $lunasCount + $deletedCount) : ($activeCount + $lunasCount) }})</span>
        </a>
    </li>
</ul>

<!-- Filter & Search Toolbar -->
<div class="card card-mdm border-0 p-3 mb-4">
    <form method="GET" action="{{ route('devices.index') }}" class="row g-2 align-items-center">
        <!-- Preserve Current Tab -->
        <input type="hidden" name="tab" value="{{ $currentTab }}">

        <!-- Search Keyword -->
        <div class="col-12 {{ auth()->user()->isBranchScoped() ? 'col-md-5' : 'col-md-4' }}">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Model, No. IMEI, Serial, Nama Pemilik, ID Anggota..." value="{{ request('search') }}">
            </div>
        </div>

        @if(!auth()->user()->isBranchScoped())
        <!-- Filter Branch (Pusat / Superadmin) -->
        <div class="col-6 col-md-3">
            <select name="branch_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                    {{ $b->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Filter State -->
        <div class="col-6 {{ auth()->user()->isBranchScoped() ? 'col-md-3' : 'col-md-2' }}">
            <select name="state" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status AMAPI</option>
                <option value="ACTIVE" {{ request('state') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                <option value="PROVISIONING" {{ request('state') == 'PROVISIONING' ? 'selected' : '' }}>PROVISIONING</option>
                <option value="DISABLED" {{ request('state') == 'DISABLED' ? 'selected' : '' }}>DISABLED</option>
                <option value="DELETED" {{ request('state') == 'DELETED' ? 'selected' : '' }}>DELETED</option>
            </select>
        </div>

        <!-- Filter Policy -->
        <div class="col-6 {{ auth()->user()->isBranchScoped() ? 'col-md-3' : 'col-md-2' }}">
            <select name="policy_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Policy</option>
                @foreach($policies as $p)
                <option value="{{ $p->policy_id }}" {{ request('policy_id') == $p->policy_id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Submit & Reset Buttons -->
        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100" title="Terapkan Filter">
                <i class="bi bi-funnel-fill"></i>
            </button>
            @if(request()->hasAny(['search', 'state', 'policy_id', 'branch_id']))
            <a href="{{ route('devices.index', ['tab' => $currentTab]) }}" class="btn btn-outline-secondary" title="Reset Filter">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Fleet Table Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
        <div>
            @if($currentTab === 'deleted')
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-archive text-secondary"></i>
                    <span>Riwayat Perangkat Di-unenroll ({{ $devices->total() }})</span>
                </h5>
                <span class="text-secondary small">Perangkat yang telah dicabut pengelolaannya atau dihapus permanen oleh Super Admin</span>
            @elseif($currentTab === 'lunas')
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-patch-check-fill text-success"></i>
                    <span>Daftar Perangkat Lunas ({{ $devices->total() }})</span>
                </h5>
                <span class="text-secondary small">Perangkat nasabah yang telah melunasi cicilan dan dilepaskan dari pembatasan MDM</span>
            @elseif($currentTab === 'all')
                <h5 class="fw-bold mb-0 text-dark">Seluruh Data Perangkat ({{ $devices->total() }})</h5>
                <span class="text-secondary small">Menampilkan data perangkat gabungan</span>
            @else
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-phone-fill text-teal"></i>
                    <span>Daftar Perangkat Aktif ({{ $devices->total() }})</span>
                </h5>
                <span class="text-secondary small">Menampilkan perangkat aktif yang sedang dalam masa pembiayaan/kredit</span>
            @endif
        </div>
        @if(auth()->user()->hasPermission('devices.sync'))
        <div>
            <form action="{{ route('devices.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Tarik Data Terbaru</span>
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">ID Anggota & Pemilik</th>
                    <th scope="col">Perangkat (Hardware)</th>
                    <th scope="col">IMEI & Kartu SIM</th>
                    <th scope="col">Policy</th>
                    <th scope="col">Status & Terakhir Lapor</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $index => $device)
                <tr class="{{ $device->state === 'DELETED' ? 'table-light text-muted' : '' }}">
                    <td class="ps-4 text-secondary small">
                        {{ $devices->firstItem() + $index }}
                    </td>
                    <td>
                        <!-- Baris 1: Indikator Online (Hijau) / Offline (Merah) + Nama Pemilik -->
                        <div class="d-flex align-items-center gap-2">
                            @if($device->state === 'DELETED')
                                <span class="status-indicator-dot deleted" title="Perangkat Dihapus / Unenrolled"></span>
                            @elseif($device->is_online)
                                <span class="status-indicator-dot online" title="Status: Online (Terhubung Aktif)"></span>
                            @else
                                <span class="status-indicator-dot offline" title="Status: Offline (Tidak Terhubung)"></span>
                            @endif
                            @if($device->owner)
                                <span class="fw-semibold {{ $device->state === 'DELETED' ? 'text-muted' : 'text-dark' }}">{{ $device->owner->nama }}</span>
                            @else
                                <span class="text-muted fst-italic small">Belum Ditautkan</span>
                            @endif
                        </div>
                        <!-- Baris 2: ID Anggota • Nama Cabang -->
                        <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                            @if($device->owner && $device->owner->id_anggota)
                                <span class="text-secondary small font-mono">{{ $device->owner->id_anggota }}</span>
                            @endif
                            @if($device->branch)
                                @if($device->owner && $device->owner->id_anggota)
                                    <span class="text-muted small">•</span>
                                @endif
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle" style="font-size: 10.5px;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ $device->branch->name }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <!-- Baris 1: Nama Komersial + Badge Mode (Sejajar) -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-semibold {{ $device->state === 'DELETED' ? 'text-muted' : 'text-dark' }}">{{ $device->human_model_name }}</span>
                            @if($device->is_work_profile)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle" style="font-size: 10px;" title="Work Profile (Personally-Owned) - IMEI dibatasi oleh Google">
                                    <i class="bi bi-person-workspace me-1"></i>Work Profile
                                </span>
                            @else
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle" style="font-size: 10px;" title="Fully Managed (Device Owner) - Milik Perusahaan">
                                    <i class="bi bi-building-check me-1"></i>Fully Managed
                                </span>
                            @endif
                        </div>
                        <!-- Baris 2: Brand • Kode Model Pabrik -->
                        <div class="text-secondary small font-mono mt-1">
                            {{ $device->brand ? ucfirst($device->brand) . ' • ' : '' }}{{ $device->hardware_model ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <!-- Baris 1: Nomor IMEI -->
                        <div>
                            @if($device->imei)
                                <span class="device-imei text-dark fw-medium">{{ $device->imei }}</span>
                            @else
                                <span class="text-muted small font-mono">-</span>
                            @endif
                        </div>
                        <!-- Baris 2: Nomor SIM Terpasang / Operator -->
                        <div class="small font-mono mt-1" style="font-size: 11px;">
                            @php
                                $simPhones = $device->detected_phone_numbers;
                                $simCards = $device->sim_cards;
                            @endphp
                            @if(!empty($simPhones))
                                <span class="text-teal fw-medium" title="Nomor Kartu SIM Terdeteksi di Perangkat">
                                    <i class="bi bi-sim-fill me-1"></i>{{ implode(', ', $simPhones) }}
                                </span>
                            @elseif(!empty($simCards))
                                <span class="text-secondary" title="Kartu SIM Terpasang ({{ $simCards[0]['carrier_name'] }})">
                                    <i class="bi bi-sim me-1"></i>{{ \Illuminate\Support\Str::limit($simCards[0]['carrier_name'], 22) }}
                                </span>
                            @else
                                <span class="text-muted">SIM: -</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($device->policy_id === 'Lunas')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle py-1 px-2 font-mono">
                                <i class="bi bi-patch-check-fill me-1"></i>Lunas
                            </span>
                        @else
                            <span class="badge-policy">
                                <i class="bi bi-shield-check me-1 text-teal"></i>
                                {{ $device->policy ? $device->policy->name : ($device->policy_id ?? 'default') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <!-- Baris 1: Badge Status AMAPI + Level Baterai (Sejajar) -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($device->state === 'ACTIVE')
                                <span class="badge badge-compliant d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-check-circle-fill"></i> ACTIVE
                                </span>
                            @elseif($device->state === 'PROVISIONING')
                                <span class="badge badge-pending-custom d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-clock-history"></i> PROVISIONING
                                </span>
                            @elseif($device->state === 'DELETED')
                                <span class="badge bg-secondary text-white d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-archive-fill"></i> DI-UNENROLL
                                </span>
                            @else
                                <span class="badge badge-warning-custom d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-exclamation-circle"></i> {{ $device->state }}
                                </span>
                            @endif

                            @if($device->state !== 'DELETED' && $device->battery_level !== null)
                                <span class="small text-dark fw-medium" title="Level Baterai Terakhir Dilaporkan ({{ $device->battery_level }}%)">
                                    <i class="bi {{ $device->battery_icon }} {{ $device->battery_color_class }}"></i> {{ $device->battery_level }}%
                                </span>
                            @endif

                            @if($device->has_location)
                                <a href="{{ $device->google_maps_url }}" target="_blank" rel="noopener" class="badge bg-teal-subtle text-teal border border-teal-subtle text-decoration-none" style="font-size: 10px;" title="Lokasi GPS Terdeteksi ({{ number_format($device->latitude, 5) }}, {{ number_format($device->longitude, 5) }}) - Klik untuk buka Google Maps">
                                    <i class="bi bi-geo-alt-fill"></i> Maps
                                </a>
                            @endif
                        </div>
                        <!-- Baris 2: Status Online (Hijau) / Offline (Merah) & Waktu Terakhir Lapor (WIB) -->
                        <div class="small font-mono mt-1 d-flex align-items-center gap-1" style="font-size: 11px;">
                            @if($device->state === 'DELETED')
                                <span class="text-secondary"><i class="bi bi-x-circle me-1"></i>Unenrolled</span>
                            @elseif($device->is_online)
                                <span class="text-success fw-bold" title="Aktif Online"><i class="bi bi-broadcast me-1"></i>Online</span>
                            @else
                                <span class="text-danger fw-bold" title="Offline / Tidak Terhubung"><i class="bi bi-dash-circle me-1"></i>Offline</span>
                            @endif
                            <span class="text-muted">•</span>
                            @if($device->last_status_report)
                                <span class="text-secondary" title="{{ $device->last_status_report->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB">
                                    <i class="bi bi-clock me-1"></i>{{ $device->last_status_report->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                            @else
                                <span class="text-muted">Belum Pernah Lapor</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('devices.show', $device->id) }}" class="btn btn-sm {{ $device->state === 'DELETED' ? 'btn-outline-secondary' : 'btn-outline-teal' }}" title="Lihat Detail & Riwayat">
                                <i class="bi bi-eye"></i> Detail
                            </a>

                            @if($device->state === 'DELETED' && auth()->user()->hasPermission('devices.delete'))
                            <form action="{{ route('devices.destroy-permanent', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus data perangkat ini secara permanen dari database?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Permanen dari Database">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        @if($currentTab === 'deleted')
                            <i class="bi bi-archive text-muted fs-1 d-block mb-2"></i>
                            Tidak ada perangkat yang berstatus di-unenroll atau dihapus.
                        @else
                            <i class="bi bi-phone-vibrate text-muted fs-1 d-block mb-2"></i>
                            Tidak ada perangkat aktif yang sesuai dengan kriteria pencarian.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($devices->hasPages())
    <div class="card-footer bg-white border-top py-3 px-4">
        {{ $devices->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
