@extends('layouts.app')

@section('title', 'Pendaftaran Perangkat Baru')
@section('page_title', 'Pendaftaran Perangkat')

@section('content')
<!-- Enrollment Card - Harmonized with MDM Dashboard Layout -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <!-- Card Header matching dashboard & policies layout -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Buat QR Code Pendaftaran Perangkat</h5>
            <span class="text-secondary small">Buat lembar barcode QR untuk mendaftarkan dan mengunci HP baru atau setelah reset pabrik</span>
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
                            <span>Pengaturan Pendaftaran Perangkat</span>
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
                                                <span class="text-secondary" style="font-size: 11px;">Perangkat yang memindai QR ini otomatis masuk ke cabang Anda</span>
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
                                            📍 {{ $b->name }} ({{ $b->code }}) — {{ $b->enterprise?->name ?? 'Pengelola Utama' }} [{{ $b->default_policy_id ?: 'Standar' }}]
                                        </option>
                                        @endforeach
                                        <option value="" {{ old('branch_id') === '' ? 'selected' : '' }}>
                                            🏢 Kantor Pusat / Umum (Pengelola Utama)
                                        </option>
                                    </select>
                                    <div class="form-text mt-1" style="font-size: 11.5px;">
                                        Pilih cabang agar perangkat yang memindai QR ini langsung terdaftar ke cabang yang bersangkutan.
                                    </div>
                                @endif
                            </div>

                            @if(auth()->user()->isBranchScoped() && auth()->user()->branch)
                            <!-- Enterprise Allocation Info for Scoped User -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">
                                    Akun Pengelola Perusahaan
                                </label>
                                <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-building-check text-teal fs-5"></i>
                                        <div>
                                            <div class="fw-bold text-dark small">
                                                {{ auth()->user()->branch->enterprise?->name ?? ($activeEnterprise?->name ?? 'Pengelola Utama') }}
                                            </div>
                                            <span class="text-muted font-mono" style="font-size: 11px;">
                                                {{ auth()->user()->branch->getEffectiveEnterpriseId() }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-secondary border font-mono">
                                        Terkait Cabang
                                    </span>
                                </div>
                            </div>
                            @endif

                            <!-- Policy Selection (Read-Only: Default Mode Kiosk or Branch Policy) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">
                                    Aturan Keamanan Perangkat <span class="text-danger">*</span>
                                </label>
                                <input type="hidden" name="policy_id" value="{{ $defaultPolicy ?? 'Mode_kiosk' }}">
                                <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 bg-teal-subtle text-teal rounded-3 border border-teal-subtle">
                                            <i class="bi bi-shield-check fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $defaultPolicy ?? 'Mode_kiosk' }}</div>
                                            <span class="text-secondary" style="font-size: 11px;">
                                                Aturan operasional standar cabang (kamera aktif, akses aplikasi aman, dan pemantau lokasi).
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono">
                                        <i class="bi bi-lock-fill me-1"></i>Otomatis Cabang
                                    </span>
                                </div>
                                <div class="form-text mt-1 text-muted" style="font-size: 11.5px;">
                                    <i class="bi bi-info-circle me-1"></i>Aturan keamanan diselaraskan otomatis dengan setelan cabang operasional.
                                </div>
                            </div>

                            <!-- Optional Pre-Assigned Owner -->
                            <div class="p-3 bg-white rounded-3 border mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0 small d-flex align-items-center gap-2">
                                        <i class="bi bi-person-check-fill text-teal"></i>
                                        <span>Data Calon Pemilik Perangkat (Opsional)</span>
                                    </h6>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle" style="font-size: 10px;">
                                        Otomatis Terhubung
                                    </span>
                                </div>
                                <p class="text-secondary mb-3" style="font-size: 11.5px; line-height: 1.35;">
                                    Isi kolom di bawah ini jika QR Code dibuat khusus untuk <strong>1 calon pemilik</strong>. Data pemilik akan langsung terhubung secara otomatis saat HP dipindai. Kosongkan jika ingin membuat <strong>QR untuk banyak HP sekaligus</strong>.
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
                                            ID Pengguna
                                        </label>
                                        <input type="text" name="id_anggota" id="id_anggota"
                                            class="form-control form-control-sm font-mono"
                                            value="{{ $suggestedId }}"
                                            readonly
                                            style="background-color: #f8fafc;">
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
                                    <strong>Petunjuk:</strong> Jika nama pemilik diisi, QR berlaku <strong>khusus untuk 1 HP</strong> agar data tidak tertukar. Jika nama dikosongkan, QR dapat digunakan untuk <strong>banyak HP secara bersamaan</strong> selama 7 hari.
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" form="enrollmentForm" class="btn btn-primary w-100 py-2 fs-6 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                        <i class="bi bi-qr-code-scan fs-5"></i>
                        <span>Buat QR Code Pendaftaran</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Panduan Registrasi Lapangan (3-Step Guide) -->
            <div class="col-12 col-lg-6">
                <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-phone text-teal fs-5"></i>
                        <span>Panduan Pendaftaran di HP Baru (Ketuk Layar 6 Kali)</span>
                    </h6>

                    <p class="text-secondary small mb-4">
                        Pendaftaran perangkat dilakukan saat HP dalam kondisi <strong>layar pembuka awal atau setelah reset pabrik</strong>.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <!-- Step 1 -->
                        <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                            <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">1</span>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 small">Nyalakan di Layar Awal ("Halo / Welcome")</h6>
                                <p class="text-secondary mb-0" style="font-size: 12px;">
                                    Nyalakan HP baru atau setelah reset pabrik. Tetap berada di layar awal ucapan selamat datang / pemilihan bahasa. Jangan lanjut ke pengaturan akun dulu.
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                            <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">2</span>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 small">Ketuk Layar Kosong 6 Kali Berturut-turut</h6>
                                <p class="text-secondary mb-0" style="font-size: 12px;">
                                    Ketuk 6 kali berturut-turut dengan cepat di area kosong layar mana saja hingga kamera pembaca QR terbuka secara otomatis.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                            <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">3</span>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 small">Pindai QR Code</h6>
                                <p class="text-secondary mb-0" style="font-size: 12px;">
                                    Arahkan kamera ke QR Code yang telah dibuat. Sambungkan ke Wi-Fi dan ikuti petunjuk di layar hingga HP selesai didaftarkan sebagai perangkat resmi perusahaan.
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
