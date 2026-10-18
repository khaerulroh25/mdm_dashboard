@extends('layouts.app')

@section('title', 'Policies Management')
@section('page_title', 'Kebijakan Keamanan (Policies)')

@section('content')
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Daftar Kebijakan Enterprise</h5>
            <span class="text-secondary small">Kelola aturan pembatasan perangkat, kontrol sistem, dan aplikasi terpasang</span>
        </div>
        <div>
            <a href="{{ route('policies.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-plus-circle"></i>
                <span>Buat Policy Baru</span>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">Nama Policy</th>
                    <th scope="col">Policy ID (AMAPI)</th>
                    <th scope="col">Perangkat Terhubung</th>
                    <th scope="col">Status Pembatasan</th>
                    <th scope="col">Mode Lokasi (GPS)</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($policies as $policy)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark">{{ $policy->name }}</div>
                        <span class="text-secondary small">{{ $policy->description ?? 'Tidak ada deskripsi' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-teal border font-mono">
                            {{ $policy->policy_id }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-phone me-1"></i> {{ $policy->devices_count }} Perangkat
                        </span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if($policy->camera_disabled)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle small" title="Kamera Dinonaktifkan">
                                    <i class="bi bi-camera-video-off"></i> No Cam
                                </span>
                            @endif
                            @if($policy->factory_reset_disabled)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle small" title="Factory Reset Dicegah">
                                    <i class="bi bi-shield-slash"></i> No Reset
                                </span>
                            @endif
                            @if($policy->wifi_config_disabled)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle small" title="Konfigurasi Wi-Fi Terkunci">
                                    <i class="bi bi-wifi-off"></i> Lock Wi-Fi
                                </span>
                            @endif
                            @if(!$policy->camera_disabled && !$policy->factory_reset_disabled && !$policy->wifi_config_disabled)
                                <span class="badge bg-light text-secondary border small">Standar</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($policy->location_mode === 'HIGH_ACCURACY')
                            <span class="badge badge-compliant"><i class="bi bi-geo-alt-fill"></i> HIGH ACCURACY</span>
                        @elseif($policy->location_mode)
                            <span class="badge bg-light text-secondary border">{{ $policy->location_mode }}</span>
                        @else
                            <span class="text-muted small">Default User</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('policies.edit', $policy->id) }}" class="btn btn-sm btn-outline-teal" title="Konfigurasi & Kelola Aplikasi">
                                <i class="bi bi-gear-fill"></i> Konfigurasi
                            </a>
                            @if($policy->policy_id !== 'default')
                            <form action="{{ route('policies.destroy', $policy->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus policy {{ $policy->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Policy" {{ $policy->devices_count > 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-secondary">
                        <i class="bi bi-shield-x text-muted fs-1 d-block mb-2"></i>
                        Belum ada policy yang dibuat.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
