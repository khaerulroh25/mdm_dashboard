@extends('layouts.app')

@section('title', 'Peringatan Perangkat Offline (>24 Jam)')
@section('page_title', 'Peringatan Perangkat Offline')

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Top Action & Info Bar -->
    <div class="card card-mdm border-0 shadow-sm overflow-hidden">
        <div class="card-body p-4 bg-white d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-sm"
                     style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                    <i class="bi bi-shield-exclamation fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge px-2 py-1 rounded-pill fw-semibold text-uppercase font-mono" style="background-color: #FEF3C7; color: #92400E; font-size: 10px; letter-spacing: 0.5px;">
                            <i class="bi bi-broadcast me-1"></i>Early Warning System
                        </span>
                        <span class="text-secondary small">&bull;</span>
                        <span class="text-secondary small">Pemantauan & Kepatuhan Perangkat Cicilan</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark font-heading">
                        Peringatan Perangkat Offline (> 24 Jam)
                    </h4>
                    <span class="text-secondary small">
                        Daftar deteksi dini nasabah yang unitnya tidak terhubung ke jaringan internet/seluler untuk tindak lanjut cepat sebelum jatuh tempo.
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm px-3 py-2" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    <span>Cetak Lembar Audit Lapangan</span>
                </button>
                <a href="{{ route('devices.offline-alerts') }}" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1 shadow-sm px-3 py-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Segarkan Data</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Standalone Executive Stats Cards -->
    <div class="row g-3">
        <!-- Card 1: Total Offline -->
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), [])) }}" 
               class="text-decoration-none">
                <div class="card card-mdm border-0 h-100 p-3 p-xl-4 transition-all position-relative overflow-hidden {{ empty($severity) ? 'shadow' : '' }}"
                     style="{{ empty($severity) ? 'border-top: 4px solid var(--bs-primary) !important;' : 'border-top: 4px solid transparent;' }}">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center text-white shadow-sm"
                             style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #0D9488 0%, #0F766E 100%);">
                            <i class="bi bi-phone-vibrate-fill fs-5"></i>
                        </div>
                        <span class="badge bg-light text-secondary border font-mono px-2 py-1" style="font-size: 10px;">
                            SEMUA DATA
                        </span>
                    </div>
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Offline (>1 Hari)</span>
                        <h2 class="fw-bold text-dark font-mono my-1">{{ number_format($totalOfflineCount) }} <small class="text-muted fs-6 fw-normal">Unit</small></h2>
                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                            <span class="text-muted" style="font-size: 11.5px;">Seluruh durasi offline</span>
                            <span class="text-teal small fw-semibold">
                                {{ empty($severity) ? '● Aktif' : 'Pilih &rarr;' }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 2: 🟡 1-3 Hari (Warning) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), ['severity' => 'warning'])) }}" 
               class="text-decoration-none">
                <div class="card card-mdm border-0 h-100 p-3 p-xl-4 transition-all position-relative overflow-hidden {{ $severity === 'warning' ? 'shadow' : '' }}"
                     style="{{ $severity === 'warning' ? 'border-top: 4px solid #F59E0B !important;' : 'border-top: 4px solid transparent;' }}">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center text-white shadow-sm"
                             style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        </div>
                        <span class="badge font-mono px-2 py-1" style="background-color: #FEF3C7; color: #92400E; font-size: 10px; border: 1px solid #FDE68A;">
                            24 – 72 JAM
                        </span>
                    </div>
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Peringatan Dini</span>
                        <h2 class="fw-bold text-dark font-mono my-1">{{ number_format($warningCount) }} <small class="text-muted fs-6 fw-normal">Unit</small></h2>
                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                            <span class="text-muted text-truncate" style="font-size: 11.5px;" title="Tindak lanjut: WhatsApp Reminder">
                                <i class="bi bi-whatsapp text-success me-1"></i>Chat WA Ramah
                            </span>
                            <span class="text-warning-emphasis small fw-semibold">
                                {{ $severity === 'warning' ? '● Aktif' : 'Filter &rarr;' }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 3: 🟠 3-7 Hari (High Risk) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), ['severity' => 'high'])) }}" 
               class="text-decoration-none">
                <div class="card card-mdm border-0 h-100 p-3 p-xl-4 transition-all position-relative overflow-hidden {{ $severity === 'high' ? 'shadow' : '' }}"
                     style="{{ $severity === 'high' ? 'border-top: 4px solid #EA580C !important;' : 'border-top: 4px solid transparent;' }}">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center text-white shadow-sm"
                             style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                        </div>
                        <span class="badge font-mono px-2 py-1" style="background-color: #FFEDD5; color: #C2410C; font-size: 10px; border: 1px solid #FED7AA;">
                            3 – 7 HARI
                        </span>
                    </div>
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Siaga Tinggi</span>
                        <h2 class="fw-bold text-dark font-mono my-1">{{ number_format($highRiskCount) }} <small class="text-muted fs-6 fw-normal">Unit</small></h2>
                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                            <span class="text-muted text-truncate" style="font-size: 11.5px;" title="Tindak lanjut: Telepon Langsung">
                                <i class="bi bi-telephone text-primary me-1"></i>Telepon Langsung
                            </span>
                            <span class="text-danger small fw-semibold">
                                {{ $severity === 'high' ? '● Aktif' : 'Filter &rarr;' }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 4: 🔴 >7 Hari (Critical) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('devices.offline-alerts', array_merge(request()->except('severity', 'page'), ['severity' => 'critical'])) }}" 
               class="text-decoration-none">
                <div class="card card-mdm border-0 h-100 p-3 p-xl-4 transition-all position-relative overflow-hidden {{ $severity === 'critical' ? 'shadow' : '' }}"
                     style="{{ $severity === 'critical' ? 'border-top: 4px solid #DC2626 !important;' : 'border-top: 4px solid transparent;' }}">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center text-white shadow-sm"
                             style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #EF4444 0%, #B91C1C 100%);">
                            <i class="bi bi-shield-fill-x fs-5"></i>
                        </div>
                        <span class="badge font-mono px-2 py-1" style="background-color: #FEE2E2; color: #991B1B; font-size: 10px; border: 1px solid #FECACA;">
                            > 7 HARI / KRITIS
                        </span>
                    </div>
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Status Kritis</span>
                        <h2 class="fw-bold text-danger font-mono my-1">{{ number_format($criticalCount) }} <small class="text-muted fs-6 fw-normal">Unit</small></h2>
                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                            <span class="text-muted text-truncate" style="font-size: 11.5px;" title="Tindak lanjut: Kunjungan Lapangan">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>Kunjungan Lapangan
                            </span>
                            <span class="text-danger small fw-semibold">
                                {{ $severity === 'critical' ? '● Aktif' : 'Filter &rarr;' }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Card: Filter & Table Listing of Offline Devices -->
    <div class="card card-mdm border-0 shadow-sm overflow-hidden">
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
