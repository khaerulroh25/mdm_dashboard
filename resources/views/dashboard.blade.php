@extends('layouts.app')

@section('title', 'Dashboard Overview')
@section('page_title', 'Enterprise Fleet Overview')

@section('content')
<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Metric 1: Total Armada -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box bg-teal-subtle">
                    <i class="bi bi-phone-fill fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Total Armada</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalDevices) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 2: Perangkat Aktif -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box" style="background-color: #ECFDF5; color: #10B981;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Aktif / Compliant</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($activeDevices) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 3: Mode Hilang / Lock -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box" style="background-color: #FFE4E6; color: #E11D48;">
                    <i class="bi bi-lock-fill fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Mode Hilang (Lost)</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($lostModeDevices) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 4: Total Aplikasi -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box" style="background-color: #F0F9FF; color: #0284C7;">
                    <i class="bi bi-google-play fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Katalog Aplikasi</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalApps) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Fleet Table -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Daftar Perangkat Terdaftar</h5>
            <span class="text-secondary small">Data tersinkronisasi langsung dari database lokal & Google AMAPI</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('devices.index') }}" class="btn btn-sm btn-outline-secondary">
                <span>Lihat Semua</span>
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">ID Anggota</th>
                    <th scope="col">Pemilik Perangkat</th>
                    <th scope="col">Tipe Perangkat</th>
                    <th scope="col">Policy Aktif</th>
                    <th scope="col">Status AMAPI</th>
                    <th scope="col">Device ID (Resource)</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDevices as $index => $device)
                <tr>
                    <td class="ps-4 text-secondary small">{{ $index + 1 }}</td>
                    <td>
                        @if($device->owner && $device->owner->id_anggota)
                            <span class="font-mono fw-semibold text-dark">{{ $device->owner->id_anggota }}</span>
                        @else
                            <span class="text-muted small">Belum ada</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $device->owner->nama ?? 'Belum Ditautkan' }}</div>
                        @if($device->owner && $device->owner->telepon)
                            <span class="text-muted small font-mono">{{ $device->owner->telepon }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $device->human_model_name }}</div>
                        <span class="text-muted small font-mono">{{ $device->hardware_model ?? '-' }}</span>
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
                    </td>
                    <td>
                        <span class="amapi-path text-muted" title="{{ $device->device_id }}">
                            .../{{ substr($device->device_id, -16) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear-fill text-secondary me-1"></i> Kelola
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('devices.show', $device->id) }}">
                                        <i class="bi bi-info-circle text-primary me-2"></i> Detail Informasi
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('devices.command', $device->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="command_type" value="LOCK">
                                        <button type="submit" class="dropdown-item py-2 text-warning">
                                            <i class="bi bi-lock-fill me-2"></i> Kunci Layar (Lock)
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('devices.command', $device->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="command_type" value="REBOOT">
                                        <button type="submit" class="dropdown-item py-2 text-warning">
                                            <i class="bi bi-arrow-clockwise me-2"></i> Reboot Device
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item py-2 text-danger" data-bs-toggle="modal" data-bs-target="#lostModeModal{{ $device->id }}">
                                        <i class="bi bi-shield-exclamation me-2"></i> Aktifkan Lost Mode
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-phone text-muted fs-1 d-block mb-2"></i>
                        Belum ada data perangkat yang tersimpan. Klik tombol <strong>Sync AMAPI</strong> di atas untuk sinkronisasi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
