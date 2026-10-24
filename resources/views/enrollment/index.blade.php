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
                            <div class="mb-3">
                                <label for="policy_id" class="form-label small fw-semibold text-secondary">
                                    Pilih Kebijakan (Policy) Awal <span class="text-danger">*</span>
                                </label>
                                <select name="policy_id" id="policy_id" class="form-select @error('policy_id') is-invalid @enderror" required>
                                    @foreach($policies as $policy)
                                    <option value="{{ $policy->policy_id }}" {{ (old('policy_id') ?? 'default') == $policy->policy_id ? 'selected' : '' }}>
                                        {{ $policy->name }} ({{ $policy->policy_id }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('policy_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Optional Pre-Assigned Owner (Solusi 1: Auto-Link saat Scan QR) -->
                            <div class="p-3 bg-white rounded-3 border mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0 small d-flex align-items-center gap-2">
                                        <i class="bi bi-person-check-fill text-teal"></i>
                                        <span>Data Calon Pemilik Perangkat (Opsional — Auto-Link)</span>
                                    </h6>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle" style="font-size: 10px;">
                                        Otomatis Terhubung
                                    </span>
                                </div>
                                <p class="text-secondary mb-3" style="font-size: 11.5px; line-height: 1.35;">
                                    Isi kolom di bawah ini jika QR Code dibuat khusus untuk <strong>1 calon pemilik</strong>. Saat HP selesai di-scan dan disinkronkan, data pemilik langsung tertaut otomatis tanpa perlu diedit manual. Kosongkan jika ingin membuat <strong>QR Massal</strong>.
                                </p>

                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label for="owner_name" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11.5px;">
                                            Nama Pemilik / Pengguna
                                        </label>
                                        <input type="text" name="owner_name" id="owner_name"
                                            class="form-control form-control-sm @error('owner_name') is-invalid @enderror"
                                            value="{{ old('owner_name') }}"
                                            placeholder="Contoh: Budi Santoso">
                                        @error('owner_name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="id_anggota" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11.5px;">
                                            ID Pengguna / Nomor Identitas <span class="text-muted fw-normal">(Wajib jika nama diisi)</span>
                                        </label>
                                        <input type="text" name="id_anggota" id="id_anggota"
                                            class="form-control form-control-sm font-mono @error('id_anggota') is-invalid @enderror"
                                            value="{{ old('id_anggota') }}"
                                            placeholder="Contoh: USR-2026001 / NIK">
                                        @error('id_anggota')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="owner_phone" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11.5px;">
                                            Nomor Telepon (Opsional)
                                        </label>
                                        <input type="text" name="owner_phone" id="owner_phone"
                                            class="form-control form-control-sm font-mono @error('owner_phone') is-invalid @enderror"
                                            value="{{ old('owner_phone') }}"
                                            placeholder="Contoh: 081234567890">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="owner_notes" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11.5px;">
                                            Catatan Singkat (Opsional)
                                        </label>
                                        <input type="text" name="owner_notes" id="owner_notes"
                                            class="form-control form-control-sm @error('owner_notes') is-invalid @enderror"
                                            value="{{ old('owner_notes') }}"
                                            placeholder="Contoh: Divisi Operasional / Lapangan">
                                    </div>
                                </div>
                            </div>

                            <!-- Information Alert -->
                            <div class="alert alert-info border-0 rounded-3 p-3 mb-4 d-flex gap-3" style="background-color: #F0FDFA; border-left: 4px solid var(--bs-primary) !important; color: #0F766E;">
                                <i class="bi bi-info-circle-fill fs-5 text-teal flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Mode Token Otomatis:</strong> Jika nama pemilik diisi, QR berlaku <strong>khusus 1 HP (Single-Use)</strong> agar tidak tertukar dengan HP lain. Jika dikosongkan, QR berlaku <strong>Massal (Multi-Device)</strong> selama 7 hari.
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
