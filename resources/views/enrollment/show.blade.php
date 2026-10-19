@extends('layouts.app')

@section('title', 'Scan QR Enrollment')
@section('page_title', 'Pendaftaran Perangkat (Enrollment)')

@section('content')
<!-- Harmonized QR Enrollment Result Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <!-- Header with Action Buttons -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Lembar QR Code Pendaftaran Perangkat</h5>
            <span class="text-secondary small">Gunakan QR Code ini untuk pendaftaran instan perangkat baru Android Enterprise (Device Owner)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('enrollment.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Buat Token Lain</span>
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
                            <span class="badge bg-teal-subtle text-teal px-3 py-2 rounded-pill fw-semibold small text-uppercase">
                                <i class="bi bi-shield-check me-1"></i> Android Enterprise Ready
                            </span>
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
                        <!-- Policy Info Badge -->
                        <div class="p-3 bg-white rounded-3 border mb-4">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <span class="text-muted small d-block" style="font-size: 11px;">Policy yang Akan Diterapkan:</span>
                                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-lock-fill text-teal"></i>
                                        <span>{{ $policy->name ?? $token->getPolicyName() }}</span>
                                    </h5>
                                </div>
                                <span class="badge bg-teal text-white font-mono px-3 py-2">
                                    ID: {{ $policy->policy_id ?? 'default' }}
                                </span>
                            </div>
                        </div>

                        <!-- 3-Step Guided Instructions -->
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-teal"></i>
                            <span>Panduan Scan di HP Baru (3 Langkah Mudah):</span>
                        </h6>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">1</span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 small">Layar Pembuka ("Welcome")</h6>
                                    <p class="text-secondary mb-0" style="font-size: 12px;">
                                        Nyalakan HP baru atau setelah factory reset di layar awal selamat datang / pemilihan bahasa.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">2</span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 small">Ketuk Layar Kosong 6 Kali</h6>
                                    <p class="text-secondary mb-0" style="font-size: 12px;">
                                        Ketuk 6 kali berturut-turut di area kosong layar mana saja hingga kamera QR scanner terbuka otomatis.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">3</span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 small">Pindai QR Code</h6>
                                    <p class="text-secondary mb-0" style="font-size: 12px;">
                                        Arahkan kamera ke QR Code di sebelah kiri. Ikuti petunjuk koneksi Wi-Fi hingga proses enrollment selesai.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist Note -->
                    <div class="alert alert-light border rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                        <i class="bi bi-lightning-charge-fill text-warning fs-5 flex-shrink-0"></i>
                        <div class="small text-secondary">
                            <strong>Tips Teknisi:</strong> Pastikan daya baterai perangkat minimal <strong>50%</strong> dan tersedia koneksi internet Wi-Fi yang stabil saat proses setup pertama kali.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide topbar, sidebar, buttons during print */
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
