@extends('layouts.app')

@section('title', 'Peringatan Perangkat Offline (>24 Jam)')
@section('page_title', 'Peringatan Perangkat Offline')

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Top Action & Info Bar -->
    <div class="card card-mdm border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                    <span>Peringatan Perangkat Offline (> 24 Jam)</span>
                </h5>
                <span class="text-secondary small">
                    Daftar pemantauan nasabah yang perangkatnya tidak terhubung ke jaringan internet/seluler lebih dari 1 hari
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    <span>Cetak Lembar Audit Lapangan</span>
                </button>
                <a href="{{ route('devices.offline-alerts') }}" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Segarkan Data</span>
                </a>
            </div>
        </div>

        <!-- 4 Summary Metric Cards (Severity Levels) -->
        <div class="card-body p-4 bg-light bg-opacity-25 border-bottom">
            <div class="row g-3">
                <!-- Total Offline -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), [])) }}" 
                       class="text-decoration-none">
                        <div class="card border rounded-3 p-3 h-100 transition-all {{ empty($severity) ? 'border-teal bg-white shadow-sm' : 'bg-white' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-secondary small fw-semibold">Total Offline (>1 Hari)</span>
                                <div class="p-2 bg-teal-subtle text-teal rounded-3">
                                    <i class="bi bi-phone-vibrate fs-5"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark font-mono mb-1">{{ number_format($totalOfflineCount) }}</h3>
                            <span class="text-muted" style="font-size: 11.5px;">Seluruh perangkat tidak aktif</span>
                        </div>
                    </a>
                </div>

                <!-- 1-3 Hari (Warning) -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), ['severity' => 'warning'])) }}" 
                       class="text-decoration-none">
                        <div class="card border rounded-3 p-3 h-100 transition-all {{ $severity === 'warning' ? 'border-warning bg-white shadow-sm ring-1 ring-warning' : 'bg-white' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-warning-emphasis small fw-semibold">Peringatan Dini</span>
                                <div class="p-2 bg-warning bg-opacity-15 text-warning rounded-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark font-mono mb-1">{{ number_format($warningCount) }}</h3>
                            <span class="text-secondary" style="font-size: 11.5px;">Offline 1 s/d 3 Hari (24–72 Jam)</span>
                        </div>
                    </a>
                </div>

                <!-- 3-7 Hari (High Risk) -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), ['severity' => 'high'])) }}" 
                       class="text-decoration-none">
                        <div class="card border rounded-3 p-3 h-100 transition-all {{ $severity === 'high' ? 'border-danger bg-white shadow-sm' : 'bg-white' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-danger small fw-semibold">Siaga Tinggi</span>
                                <div class="p-2 bg-danger bg-opacity-10 text-danger rounded-3">
                                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark font-mono mb-1">{{ number_format($highRiskCount) }}</h3>
                            <span class="text-secondary" style="font-size: 11.5px;">Offline 3 s/d 7 Hari</span>
                        </div>
                    </a>
                </div>

                <!-- >7 Hari (Critical) -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), ['severity' => 'critical'])) }}" 
                       class="text-decoration-none">
                        <div class="card border rounded-3 p-3 h-100 transition-all {{ $severity === 'critical' ? 'border-danger bg-danger bg-opacity-10 shadow-sm' : 'bg-white' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-danger small fw-semibold">Status Kritis</span>
                                <div class="p-2 bg-danger text-white rounded-3">
                                    <i class="bi bi-shield-fill-x fs-5"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-danger font-mono mb-1">{{ number_format($criticalCount) }}</h3>
                            <span class="text-secondary" style="font-size: 11.5px;">Offline > 7 Hari / Belum Lapor</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card-body p-4 bg-white border-bottom">
            <form action="{{ route('devices.offline-alerts') }}" method="GET" class="row g-3 align-items-center">
                @if(!empty($severity))
                    <input type="hidden" name="severity" value="{{ $severity }}">
                @endif

                <!-- Search Input -->
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-secondary border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                               value="{{ $search }}" 
                               placeholder="Cari nama nasabah, NIK, nomor telepon, IMEI, atau tipe HP...">
                    </div>
                </div>

                <!-- Branch Filter (for Superadmin) -->
                @if(!auth()->user()->isBranchScoped())
                <div class="col-12 col-md-3">
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Cabang Operasional --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                📍 {{ $b->name }} ({{ $b->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="col-12 col-md-3">
                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-3 py-2">
                        <i class="bi bi-geo-alt-fill me-1"></i> Cabang: {{ auth()->user()->branch?->name }}
                    </span>
                </div>
                @endif

                <!-- Submit / Reset Buttons -->
                <div class="col-12 col-md-4 d-flex align-items-center gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-teal btn-sm px-3 d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-filter"></i>
                        <span>Terapkan Filter</span>
                    </button>
                    @if($search || $selectedBranchId || $severity)
                    <a href="{{ route('devices.offline-alerts') }}" class="btn btn-outline-secondary btn-sm px-2" title="Reset Semua Filter">
                        <i class="bi bi-x-circle"></i>
                        <span>Reset</span>
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Listing of Offline Devices -->
        <div class="table-responsive" id="printableArea">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-tosca">
                    <tr>
                        <th scope="col" class="ps-4" style="width: 50px;">No</th>
                        <th scope="col">Nasabah / Calon Pemilik</th>
                        <th scope="col">Kontak & Follow-up</th>
                        <th scope="col">Cabang</th>
                        <th scope="col">Perangkat & IMEI</th>
                        <th scope="col">Durasi Offline</th>
                        <th scope="col">Lokasi Terakhir</th>
                        <th scope="col" class="text-end pe-4">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $index => $device)
                    @php
                        $risk = $device->offline_risk;
                        $owner = $device->owner;
                        $waUrl = $device->whatsapp_alert_url;
                        $phone = $device->preferred_whatsapp_number;
                    @endphp
                    <tr>
                        <td class="ps-4 text-secondary small">
                            {{ $devices->firstItem() + $index }}
                        </td>

                        <!-- Nasabah / Pemilik -->
                        <td>
                            @if(!empty($owner?->nama))
                                <div class="fw-bold text-dark fs-6">{{ $owner->nama }}</div>
                                <div class="font-mono text-muted small" style="font-size: 11px;">
                                    ID: <strong>{{ $owner->id_anggota ?: '-' }}</strong>
                                </div>
                            @else
                                <span class="badge bg-light text-muted border">Belum Ditautkan</span>
                            @endif
                        </td>

                        <!-- Kontak & WhatsApp Action -->
                        <td>
                            @if($phone)
                                <div class="font-mono small text-dark mb-1">
                                    <i class="bi bi-telephone text-secondary me-1"></i>{{ $phone }}
                                </div>
                                @if($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" 
                                       class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1 text-white text-decoration-none" 
                                       style="font-size: 11.5px; background-color: #25D366; border-color: #25D366;">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Chat WA Reminder</span>
                                    </a>
                                @endif
                            @else
                                <span class="text-muted small">Nomor Tidak Tersedia</span>
                            @endif
                        </td>

                        <!-- Cabang -->
                        <td>
                            @if($device->branch)
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono">
                                    {{ $device->branch->code }}
                                </span>
                                <div class="small text-dark mt-1" style="font-size: 11.5px;">{{ $device->branch->name }}</div>
                            @else
                                <span class="text-muted small">Pusat / Tanpa Cabang</span>
                            @endif
                        </td>

                        <!-- Perangkat & IMEI -->
                        <td>
                            <div class="fw-semibold text-dark small">{{ $device->display_name }}</div>
                            <div class="font-mono text-muted" style="font-size: 11px;">
                                IMEI: {{ $device->imei ?: '-' }}
                            </div>
                            @if($device->battery_level !== null)
                                <div class="small mt-1 d-inline-flex align-items-center gap-1 {{ $device->battery_color_class }}" style="font-size: 11px;">
                                    <i class="bi {{ $device->battery_icon }}"></i>
                                    <span>Baterai Terakhir: {{ $device->battery_level }}%</span>
                                </div>
                            @endif
                        </td>

                        <!-- Durasi Offline -->
                        <td>
                            <div class="mb-1">
                                <span class="badge {{ $risk['badge_subtle'] }} py-1 px-2 font-mono">
                                    <i class="bi {{ $risk['icon'] }} me-1"></i>
                                    Offline {{ $risk['days'] }} Hari
                                </span>
                            </div>
                            <div class="text-muted" style="font-size: 11px;">
                                Terakhir: 
                                <strong>
                                    {{ $device->last_status_report ? $device->last_status_report->format('d M Y, H:i') : 'Belum Pernah' }}
                                </strong>
                            </div>
                        </td>

                        <!-- Lokasi Terakhir -->
                        <td>
                            @if($device->has_location)
                                <a href="{{ $device->google_maps_url }}" target="_blank" 
                                   class="btn btn-sm btn-outline-teal d-inline-flex align-items-center gap-1 px-2 py-1" 
                                   style="font-size: 11.5px;">
                                    <i class="bi bi-geo-alt-fill text-danger"></i>
                                    <span>Buka Maps</span>
                                </a>
                                @if($device->location_updated_at)
                                <div class="text-muted mt-1" style="font-size: 10.5px;">
                                    {{ $device->location_updated_at->diffForHumans() }}
                                </div>
                                @endif
                            @else
                                <span class="text-muted small d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt"></i> Belum ada data
                                </span>
                            @endif
                        </td>

                        <!-- Action: Detail Perangkat Only (No Lock/Pre-Lock) -->
                        <td class="text-end pe-4">
                            <a href="{{ route('devices.show', $device->id) }}" 
                               class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" 
                               title="Lihat Detail Teknis Perangkat">
                                <i class="bi bi-eye"></i>
                                <span class="d-none d-md-inline">Detail</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle mb-3">
                                    <i class="bi bi-shield-check fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Seluruh Perangkat Beroperasi Normal</h6>
                                <p class="text-muted small mb-0" style="max-width: 480px;">
                                    Tidak ada perangkat cicilan aktif yang terdeteksi offline lebih dari 24 jam pada kriteria filter saat ini.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
        <div class="card-footer bg-white py-3 px-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-secondary small">
                Menampilkan <strong>{{ $devices->firstItem() }}</strong> sampai <strong>{{ $devices->lastItem() }}</strong> dari <strong>{{ $devices->total() }}</strong> perangkat offline
            </div>
            <div>
                {{ $devices->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printableArea, #printableArea * {
        visibility: visible;
    }
    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
    }
}
</style>
@endsection
