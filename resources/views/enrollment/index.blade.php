@extends('layouts.app')

@section('title', 'Pendaftaran Perangkat Baru (Enrollment)')
@section('page_title', 'Pendaftaran Perangkat (Enrollment)')

@section('content')
<!-- Enrollment Card - Harmonized with MDM Dashboard Layout -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <!-- Card Header matching dashboard & policies layout -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Buat QR Code Pendaftaran Perangkat</h5>
            <span class="text-secondary small">Generate enrollment token & QR Code Google Cloud AMAPI untuk aktivasi HP baru / setelah reset</span>
        </div>
        <div>
            <a href="{{ route('devices.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-phone"></i>
                <span>Lihat Daftar Perangkat</span>
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Left Column: Form Pembuatan Token -->
            <div class="col-12 col-lg-6">
                <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-qr-code text-teal fs-5"></i>
                            <span>Konfigurasi Token Pendaftaran</span>
                        </h6>

                        <form action="{{ route('enrollment.generate') }}" method="POST" id="enrollmentForm">
                            @csrf

                            <!-- Branch Selection / Info -->
                            <div class="mb-3">
                                <label for="branch_id" class="form-label small fw-semibold text-secondary">
                                    Cabang Tujuan Pendaftaran Perangkat <span class="text-danger">*</span>
                                </label>
                                @if(auth()->user()->isBranchScoped() && auth()->user()->branch)
                                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                    <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-geo-alt-fill text-teal fs-5"></i>
                                            <div>
                                                <div class="fw-bold text-dark small">{{ auth()->user()->branch->name }}</div>
                                                <span class="text-secondary" style="font-size: 11px;">Perangkat yang men-scan QR ini otomatis masuk ke cabang Anda</span>
                                            </div>
                                        </div>
                                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono">
                                            {{ auth()->user()->branch->code }}
                                        </span>
                                    </div>
                                @else
                                    <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                        @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                            📍 {{ $b->name }} ({{ $b->code }})
                                        </option>
                                        @endforeach
                                        <option value="" {{ old('branch_id') === '' ? 'selected' : '' }}>
                                            🏢 Pusat / Tanpa Cabang Spesifik
                                        </option>
                                    </select>
                                    <div class="form-text mt-1" style="font-size: 11.5px;">
                                        Pilih cabang agar perangkat yang memindai QR ini otomatis ditempatkan ke cabang tersebut saat sinkronisasi.
                                    </div>
                                @endif
                            </div>

                            <!-- Policy Selection -->
                            <div class="mb-4">
                                <label for="policy_id" class="form-label small fw-semibold text-secondary">
                                    Pilih Kebijakan (Policy) Awal <span class="text-danger">*</span>
                                </label>
                                <select name="policy_id" id="policy_id" class="form-select form-select-lg @error('policy_id') is-invalid @enderror" required>
                                    @foreach($policies as $policy)
                                    <option value="{{ $policy->policy_id }}" {{ (old('policy_id') ?? 'default') == $policy->policy_id ? 'selected' : '' }}>
                                        {{ $policy->name }} ({{ $policy->policy_id }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-2" style="font-size: 11.5px; line-height: 1.4;">
                                    Perangkat yang memindai QR code ini akan secara otomatis menerapkan seluruh batasan sistem, mode Play Store, dan aplikasi yang terdaftar pada kebijakan ini.
                                </div>
                                @error('policy_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Information Alert -->
                            <div class="alert alert-info border-0 rounded-3 p-3 mb-4 d-flex gap-3" style="background-color: #F0FDFA; border-left: 4px solid var(--bs-primary) !important; color: #0F766E;">
                                <i class="bi bi-info-circle-fill fs-5 text-teal flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Masa Berlaku Token:</strong> Token pendaftaran yang dihasilkan aktif selama <strong>7 hari</strong> secara default dan dapat digunakan untuk mendaftarkan <strong>banyak perangkat sekaligus</strong> tanpa batas.
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" form="enrollmentForm" class="btn btn-primary w-100 py-2 fs-6 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                        <i class="bi bi-qr-code-scan fs-5"></i>
                        <span>Generate Enrollment QR Code</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Panduan Registrasi Lapangan (3-Step Guide) -->
            <div class="col-12 col-lg-6">
                <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-phone text-teal fs-5"></i>
                        <span>Panduan Scan di HP Baru (6-Tap Enrollment)</span>
                    </h6>

                    <p class="text-secondary small mb-4">
                        Pendaftaran perangkat Android Enterprise dilakukan saat HP dalam kondisi <strong>setelan pabrik (Out-of-Box / Factory Reset)</strong>.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <!-- Step 1 -->
                        <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                            <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">1</span>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 small">Nyalakan di Layar Awal ("Welcome")</h6>
                                <p class="text-secondary mb-0" style="font-size: 12px;">
                                    Nyalakan HP baru atau setelah factory reset, tetap berada di layar ucapan selamat datang / pemilihan bahasa ("Welcome"). Jangan lanjut ke pengaturan akun dulu.
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                            <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">2</span>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 small">Ketuk Layar Kosong 6 Kali Berturut-turut</h6>
                                <p class="text-secondary mb-0" style="font-size: 12px;">
                                    Ketuk 6 kali berturut-turut di area kosong layar manapun. Fitur bawaan Android akan mendownload QR code reader Google secara otomatis.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                            <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">3</span>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 small">Arahkan Kamera ke QR Code</h6>
                                <p class="text-secondary mb-0" style="font-size: 12px;">
                                    Pindai QR Code hasil generate token. Perangkat akan terhubung ke Wi-Fi dan langsung terdaftar ke sistem MDM sebagai <strong>Company-Owned (Device Owner)</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
