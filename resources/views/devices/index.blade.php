@extends('layouts.app')

@section('title', 'Devices Fleet')
@section('page_title', 'Manajemen Armada Perangkat')

@section('content')
<!-- Filter & Search Toolbar -->
<div class="card card-mdm border-0 p-3 mb-4">
    <form method="GET" action="{{ route('devices.index') }}" class="row g-2 align-items-center">
        <!-- Search Keyword -->
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Model, No. IMEI, Serial, Nama Pemilik, ID Anggota..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- Filter State -->
        <div class="col-6 col-md-3">
            <select name="state" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status AMAPI</option>
                <option value="ACTIVE" {{ request('state') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                <option value="PROVISIONING" {{ request('state') == 'PROVISIONING' ? 'selected' : '' }}>PROVISIONING</option>
                <option value="DISABLED" {{ request('state') == 'DISABLED' ? 'selected' : '' }}>DISABLED</option>
            </select>
        </div>

        <!-- Filter Policy -->
        <div class="col-6 col-md-3">
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
            @if(request()->hasAny(['search', 'state', 'policy_id']))
            <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
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
            <h5 class="fw-bold mb-0 text-dark">Daftar Perangkat ({{ $devices->total() }})</h5>
            <span class="text-secondary small">Menampilkan data armada yang terhubung dengan Google Cloud</span>
        </div>
        <div>
            <form action="{{ route('devices.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Tarik Data Terbaru</span>
                </button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">ID Anggota & Pemilik</th>
                    <th scope="col">Perangkat (Hardware)</th>
                    <th scope="col">No. IMEI</th>
                    <th scope="col">Policy Aktif</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $index => $device)
                <tr>
                    <td class="ps-4 text-secondary small">
                        {{ $devices->firstItem() + $index }}
                    </td>
                    <td>
                        @if($device->owner)
                            <div class="d-flex align-items-center gap-1">
                                <span class="rounded-circle {{ $device->is_online ? 'bg-success' : 'bg-secondary' }}" style="width: 8px; height: 8px; display: inline-block;" title="{{ $device->online_status_label }}"></span>
                                <div class="fw-semibold text-dark">{{ $device->owner->nama }}</div>
                            </div>
                            <span class="badge bg-light text-secondary border font-mono small">{{ $device->owner->id_anggota }}</span>
                            <div class="small font-mono {{ $device->is_online ? 'text-success' : 'text-muted' }}" style="font-size: 11px;">
                                <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>{{ $device->online_status_label }}
                            </div>
                        @else
                            <span class="badge bg-light text-muted border">Belum Ditautkan</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $device->human_model_name }}</div>
                        <span class="text-secondary small font-mono">
                            {{ $device->brand ? ucfirst($device->brand) . ' • ' : '' }}{{ $device->hardware_model ?? '-' }}
                        </span>
                        <div class="mt-1">
                            @if($device->is_work_profile)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle small" title="Work Profile (Personally-Owned) - IMEI dibatasi oleh Google">
                                    <i class="bi bi-person-workspace me-1"></i>Work Profile
                                </span>
                            @else
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle small" title="Fully Managed (Device Owner) - Milik Perusahaan">
                                    <i class="bi bi-building-check me-1"></i>Fully Managed
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($device->imei)
                            <span class="device-imei text-dark fw-medium">{{ $device->imei }}</span>
                        @else
                            <span class="text-muted small font-mono">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-policy">
                            <i class="bi bi-shield-check me-1 text-teal"></i>
                            {{ $device->policy ? $device->policy->name : ($device->policy_id ?? 'default') }}
                        </span>
                    </td>
                    <td>
                        @if($device->state === 'ACTIVE')
                            <span class="badge badge-compliant d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i> ACTIVE
                            </span>
                        @elseif($device->state === 'PROVISIONING')
                            <span class="badge badge-pending-custom d-inline-flex align-items-center gap-1">
                                <i class="bi bi-clock-history"></i> PROVISIONING
                            </span>
                        @else
                            <span class="badge badge-warning-custom d-inline-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ $device->state }}
                            </span>
                        @endif
                        @if($device->battery_level !== null)
                            <div class="mt-1 small text-dark fw-medium" title="Level Baterai Terakhir Dilaporkan">
                                <i class="bi bi-battery-charging text-success me-1"></i>{{ $device->battery_level }}%
                            </div>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('devices.show', $device->id) }}" class="btn btn-sm btn-outline-teal" title="Lihat Detail & Perintah Remote">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-phone-vibrate text-muted fs-1 d-block mb-2"></i>
                        Tidak ada perangkat yang sesuai dengan kriteria pencarian.
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
