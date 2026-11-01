@extends('layouts.app')

@section('title', 'Scan QR Enrollment')
@section('page_title', 'Pendaftaran Perangkat (Enrollment)')

@section('content')
<!-- Harmonized QR Enrollment Result Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4 shadow-sm">
    <!-- Header with Action Buttons -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-qr-code text-teal"></i>
                <span>Lembar QR Code Pendaftaran Perangkat</span>
            </h5>
            <span class="text-secondary small">Gunakan QR Code ini untuk pendaftaran instan perangkat Android Enterprise (Device Owner)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('enrollment.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali / Buat Token Baru</span>
            </a>
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span>Cetak Lembar QR</span>
            </button>
        </div>
    </div>

    <div class="card-body p-4" id="printableArea">
        <div class="row g-4 align-items-stretch">
            <!-- Left Column: Printable QR Card -->
            <div class="col-12 col-lg-5">
                <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 text-center d-flex flex-column justify-content-between">
                    <div>
                        <div class="mb-3">
                            @if(($engine ?? 'standalone') === 'standalone')
                                <span class="badge bg-teal-subtle text-teal px-3 py-2 rounded-pill fw-semibold small text-uppercase">
                                    <i class="bi bi-shield-check me-1"></i> In-House MDM (Custom DPC)
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold small text-uppercase">
                                    <i class="bi bi-cloud me-1"></i> Google Cloud AMAPI
                                </span>
                            @endif
                        </div>

                        <!-- QR Container with Dashed Tosca Border -->
                        <div class="d-inline-block p-3 bg-white rounded-4 shadow-sm mb-3" style="border: 2px dashed var(--bs-primary); max-width: 100%;">
                            <img src="{{ $base64Image }}" alt="Enrollment QR Code" class="img-fluid rounded-2" style="width: 250px; height: 250px; object-fit: contain;">

                            <div class="mt-3 pt-2 border-top">
                                <span class="text-muted small d-block mb-1" style="font-size: 11px;">Token Value (Manual Setup):</span>
                                <div class="font-mono fw-bold fs-6 text-dark user-select-all bg-light py-1 px-2 rounded border">
                                    {{ $tokenValue }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiration Notice -->
                    <div class="text-secondary small py-2 px-3 bg-white rounded-3 border d-inline-flex align-items-center justify-content-center gap-2 mx-auto">
                        <i class="bi bi-clock-history text-teal"></i>
                        <span>Berlaku hingga: <strong class="text-dark">{{ date('d M Y, H:i', strtotime($expiration)) }} WIB</strong></span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Detail Kebijakan & Panduan Lapangan -->
            <div class="col-12 col-lg-7">
                <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Policy & Branch Info Badge -->
                        <div class="p-3 bg-white rounded-3 border mb-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <span class="text-muted small d-block" style="font-size: 11px;">Policy yang Akan Diterapkan:</span>
                                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-lock-fill text-teal"></i>
                                        <span>{{ $policy->name ?? ($policy->policy_id ?? 'Mode_kiosk') }}</span>
                                    </h5>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @if(!empty($branch))
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-3 py-2">
                                        <i class="bi bi-geo-alt-fill me-1"></i> {{ $branch->name }}
                                    </span>
                                    @endif
                                    <span class="badge bg-teal text-white font-mono px-3 py-2">
                                        ID: {{ $policy->policy_id ?? 'default' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if(!empty($ownerPreview))
                        <!-- Pre-Assigned Owner Info Card -->
                        <div class="p-3 bg-white rounded-3 border mb-3" style="border-left: 4px solid var(--bs-primary) !important;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <span class="text-muted small d-block" style="font-size: 11px;">Calon Pemilik Perangkat (Otomatis Ditautkan Saat Scan):</span>
                                    <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2 mt-1">
                                        <i class="bi bi-person-check-fill text-teal"></i>
                                        <span>{{ $ownerPreview['nama'] }}</span>
                                    </div>
                                    <div class="text-secondary small font-mono mt-1">
                                        ID Pengguna: <strong>{{ $ownerPreview['id_anggota'] }}</strong>
                                        @if(!empty($ownerPreview['telepon']))
                                            • Telp: {{ $ownerPreview['telepon'] }}
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle px-2 py-1 small">
                                    <i class="bi bi-1-circle-fill text-warning me-1"></i> Khusus 1 HP (Single-Use)
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- Testing in Emulator / ADB Command -->
                        @if(($engine ?? 'standalone') === 'standalone')
                        <div class="p-3 bg-white rounded-3 border mb-3">
                            <h6 class="fw-bold text-dark mb-2 small d-flex align-items-center gap-2">
                                <i class="bi bi-terminal text-teal"></i>
                                <span>Perintah Simulasi di Emulator (Tanpa Scan Kamera)</span>
                            </h6>
                            <p class="text-muted mb-2" style="font-size: 11px;">
                                Jalankan cURL berikut di terminal host untuk menyimulasikan pendaftaran emulator Android Anda:
                            </p>
                            <div class="position-relative">
                                <pre class="bg-dark text-success p-2 rounded small font-mono mb-0 user-select-all" style="font-size: 10.5px; overflow-x: auto;">curl -X POST "{{ url('/api/v1/dpc/enroll') }}" \
  -H "Content-Type: application/json" \
  -d '{"enrollment_token":"{{ $tokenValue }}","hardware_model":"Pixel 6 Emulator","brand":"Google","serial_number":"EMU_{{ rand(1000, 9999) }}"}'</pre>
                            </div>
                        </div>
                        @endif

                        <!-- 3-Step Guided Instructions -->
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-teal"></i>
                            <span>Panduan Scan di HP Baru (3 Langkah Mudah):</span>
                        </h6>

                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="bg-white p-2 px-3 rounded-3 border d-flex gap-3 align-items-start">
                                <span class="badge bg-teal text-white rounded-circle p-1 px-2 fw-bold" style="font-size: 11px;">1</span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 small">Layar Pembuka ("Welcome")</h6>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px;">
                                        Nyalakan HP baru di layar pembuka "Welcome" / pemilihan bahasa.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white p-2 px-3 rounded-3 border d-flex gap-3 align-items-start">
                                <span class="badge bg-teal text-white rounded-circle p-1 px-2 fw-bold" style="font-size: 11px;">2</span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 small">Ketuk Layar Kosong 6 Kali</h6>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px;">
                                        Ketuk 6 kali berturut-turut di area kosong layar manapun hingga kamera QR scanner terbuka otomatis.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white p-2 px-3 rounded-3 border d-flex gap-3 align-items-start">
                                <span class="badge bg-teal text-white rounded-circle p-1 px-2 fw-bold" style="font-size: 11px;">3</span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 small">Pindai QR Code</h6>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px;">
                                        Arahkan kamera ke QR Code di sebelah kiri hingga proses pendaftaran selesai.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Provisioning Payload Accordion -->
                        <div class="accordion" id="accordionPayload">
                            <div class="accordion-item border rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="headingPayload">
                                    <button class="accordion-button collapsed py-2 px-3 small bg-white text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayload">
                                        <i class="bi bi-code-square me-2 text-teal"></i> Lihat Raw Provisioning JSON
                                    </button>
                                </h2>
                                <div id="collapsePayload" class="accordion-collapse collapse" data-bs-parent="#accordionPayload">
                                    <div class="accordion-body bg-light p-2 font-mono small user-select-all" style="font-size: 10px; max-height: 140px; overflow-y: auto;">
                                        <pre class="mb-0 text-dark">{{ json_encode($provisioningPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist Note -->
                    <div class="alert alert-light border rounded-3 p-2 px-3 mb-0 d-flex align-items-center gap-2 mt-3">
                        <i class="bi bi-lightning-charge-fill text-warning flex-shrink-0"></i>
                        <div class="small text-secondary" style="font-size: 11px;">
                            <strong>Tips:</strong> Pastikan baterai minimal 50% dan tersedia koneksi internet saat proses setup pertama kali.
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 !important;
    }
}
</style>
@endsection
