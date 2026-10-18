@extends('layouts.app')

@section('title', 'Scan QR Enrollment')
@section('page_title', 'QR Code Pendaftaran Perangkat')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <a href="{{ route('enrollment.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali">
        <i class="bi bi-arrow-left me-1"></i> Buat Token Lain
    </a>
    <button type="button" class="btn btn-teal btn-sm d-flex align-items-center gap-1" onclick="window.print()">
        <i class="bi bi-printer"></i>
        <span>Cetak Lembar QR</span>
    </button>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-xl-8">
        <!-- Printable QR Card with Tosca Border -->
        <div class="card card-mdm border-0 p-4 p-md-5 text-center mb-4" id="printableArea">
            <div class="mb-3">
                <span class="badge bg-teal-subtle text-teal px-3 py-2 rounded-pill fw-semibold small text-uppercase">
                    <i class="bi bi-shield-check me-1"></i> Google Android Enterprise Enrollment
                </span>
            </div>

            <h3 class="fw-bold text-dark mb-1">Pendaftaran Perangkat Baru</h3>
            <p class="text-secondary small mb-4">
                Policy Diterapkan: <strong class="text-teal">{{ $policy->name ?? $token->getPolicyName() }}</strong>
            </p>

            <!-- QR Container with 2px dashed Tosca border -->
            <div class="d-inline-block p-4 bg-white rounded-4 shadow-sm mb-4" style="border: 2px dashed #0D9488; max-width: 380px;">
                <img src="{{ $base64Image }}" alt="Enrollment QR Code" class="img-fluid rounded-2" style="width: 280px; height: 280px;">
                <div class="mt-3 pt-2 border-top">
                    <span class="text-muted small d-block" style="font-size: 11px;">Token Value (Manual Setup):</span>
                    <span class="font-mono fw-bold fs-6 text-dark letter-spacing-1">{{ $tokenValue }}</span>
                </div>
            </div>

            <!-- Expiration Notice -->
            <div class="text-secondary small mb-4">
                <i class="bi bi-clock-history me-1"></i> Berlaku hingga:
                <strong class="text-dark">{{ date('d M Y, H:i', strtotime($expiration)) }} WIB</strong>
            </div>

            <!-- 3-Step Field Instructions from UI Design System -->
            <div class="card bg-light border-0 rounded-3 p-4 text-start">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-info-circle text-teal me-2"></i>Panduan Pendaftaran di Perangkat Baru (3 Langkah):
                </h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge bg-primary text-white rounded-circle p-2 px-3 fw-bold">1</span>
                            <div class="small">
                                <strong>Layar Pembuka:</strong><br>
                                Nyalakan HP baru di layar "Welcome" setelah factory reset.
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge bg-primary text-white rounded-circle p-2 px-3 fw-bold">2</span>
                            <div class="small">
                                <strong>Ketuk Layar 6 Kali:</strong><br>
                                Ketuk 6 kali berturut-turut di area kosong layar mana saja hingga kamera QR scanner terbuka.
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge bg-primary text-white rounded-circle p-2 px-3 fw-bold">3</span>
                            <div class="small">
                                <strong>Arahkan Kamera:</strong><br>
                                Pindai QR Code di atas dan ikuti instruksi sambungan Wi-Fi otomatis.
                            </div>
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
    }
}
</style>
@endsection
