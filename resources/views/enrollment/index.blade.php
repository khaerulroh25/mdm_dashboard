@extends('layouts.app')

@section('title', 'Pendaftaran Perangkat Baru (Enrollment)')
@section('page_title', 'Pendaftaran Perangkat (Enrollment)')

@section('content')
<!-- Enrollment Card - Harmonized with MDM Dashboard Layout -->
<div class="card card-mdm border-0 overflow-hidden mb-4 shadow-sm">
    <!-- Card Header matching dashboard & policies layout -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-qr-code-scan text-teal"></i>
                <span>Buat QR Code Pendaftaran Perangkat</span>
            </h5>
            <span class="text-secondary small">Generate enrollment token & QR Code Android Enterprise untuk aktivasi perangkat baru / setelah factory reset</span>
        </div>
        <div>
            <a href="{{ route('devices.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-phone"></i>
                <span>Lihat Daftar Perangkat</span>
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        @if(session('error'))
        <div class="alert alert-danger border-0 rounded-3 p-3 mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success border-0 rounded-3 p-3 mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-check-circle-fill fs-5 text-success flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        <div class="row g-4">
            <!-- Left Column: Form Pembuatan Token -->
            <div class="col-12 col-lg-6">
                <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-gear-fill text-teal fs-5"></i>
                            <span>Konfigurasi Token Pendaftaran</span>
                        </h6>

                        <form action="{{ route('enrollment.generate') }}" method="POST" id="enrollmentForm">
                            @csrf
                            <input type="hidden" name="engine" value="standalone" id="engine_standalone">

                            <!-- Engine Info Banner -->
                            <div class="mb-3">
                                <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between" style="border-color: #0D9488 !important; background-color: #F0FDFA;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; min-width: 36px;">
                                            <i class="bi bi-shield-check fs-6"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block small">In-House Standalone MDM Engine</span>
                                            <span class="text-secondary" style="font-size: 11px;">100% Native DPC Client &bull; Zero-Touch QR Provisioning &bull; Bebas Ketergantungan Google Cloud</span>
                                        </div>
                                    </div>
                                    <span class="badge bg-teal text-white px-2 py-1" style="font-size: 11px;">
                                        <i class="bi bi-star-fill me-1"></i> Mandiri
                                    </span>
                                </div>
                            </div>

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
                                            📍 {{ $b->name }} ({{ $b->code }}) — [{{ $b->default_policy_id ?: 'Mode_kiosk' }}]
                                        </option>
                                        @endforeach
                                        <option value="" {{ old('branch_id') === '' ? 'selected' : '' }}>
                                            🏢 Pusat / Tanpa Cabang Spesifik
                                        </option>
                                    </select>
                                    <div class="form-text mt-1" style="font-size: 11.5px;">
                                        Pilih cabang agar perangkat yang memindai QR ini otomatis teralokasi ke cabang bersangkutan.
                                    </div>
                                @endif
                            </div>

                            <!-- Policy Selection -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">
                                    Kebijakan (Policy) Pendaftaran <span class="text-danger">*</span>
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
                                                Kebijakan operasional standar (Kiosk Mode, proteksi reset pabrik, pelacakan baterai & telemetri).
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono">
                                        <i class="bi bi-lock-fill me-1"></i>Otomatis Cabang
                                    </span>
                                </div>
                            </div>

                            <!-- Optional Wi-Fi Auto-Connect Provisioning -->
                            <div class="p-3 bg-white rounded-3 border mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0 small d-flex align-items-center gap-2">
                                        <i class="bi bi-wifi text-teal"></i>
                                        <span>Konfigurasi Wi-Fi Otomatis (Opsional)</span>
                                    </h6>
                                    <span class="badge bg-light text-secondary border" style="font-size: 10px;">
                                        Auto-Connect Saat Scan
                                    </span>
                                </div>
                                <p class="text-secondary mb-2" style="font-size: 11.5px; line-height: 1.35;">
                                    Sertakan nama Wi-Fi & password ke dalam QR Code agar perangkat otomatis terkoneksi ke internet saat pertama kali di-scan tanpa perlu mengetik manual di HP.
                                </p>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <input type="text" name="wifi_ssid" id="wifi_ssid" class="form-control form-control-sm" placeholder="Nama Wi-Fi (SSID)" value="{{ old('wifi_ssid') }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="password" name="wifi_password" id="wifi_password" class="form-control form-control-sm" placeholder="Password Wi-Fi" value="{{ old('wifi_password') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Pre-Assigned Owner -->
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
                                    Isi kolom di bawah ini jika QR Code dibuat khusus untuk <strong>1 calon pemilik</strong>. Saat perangkat selesai di-enroll, data pemilik langsung tertaut otomatis. Kosongkan jika ingin membuat <strong>QR Massal</strong>.
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
                                            ID Pengguna / NIK <span class="text-muted fw-normal">(Wajib jika nama diisi)</span>
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
                                    <strong>Mode Token:</strong> Jika nama pemilik diisi, QR berlaku <strong>khusus 1 Perangkat (Single-Use)</strong>. Jika dikosongkan, QR berlaku <strong>Massal (Multi-Device)</strong> selama 7 hari.
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" form="enrollmentForm" class="btn btn-primary w-100 py-2 fs-6 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                        <i class="bi bi-qr-code fs-5"></i>
                        <span>Generate Enrollment QR Code</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Panduan Registrasi Lapangan (3-Step Guide) -->
            <div class="col-12 col-lg-6">
                <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-phone text-teal fs-5"></i>
                            <span>Panduan Registrasi Perangkat (Device Owner)</span>
                        </h6>

                        <!-- Nav tabs for Physical vs Emulator instructions -->
                        <ul class="nav nav-pills mb-3 gap-2" id="guideTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active btn-sm py-1 px-3" id="physical-tab" data-bs-toggle="pill" data-bs-target="#physical-guide" type="button" role="tab">
                                    <i class="bi bi-phone me-1"></i> HP Fisik (Scan QR)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link btn-sm py-1 px-3" id="emulator-tab" data-bs-toggle="pill" data-bs-target="#emulator-guide" type="button" role="tab">
                                    <i class="bi bi-laptop me-1"></i> Emulator Android (ADB)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="guideTabContent">
                            <!-- Physical Phone Guide -->
                            <div class="tab-pane fade show active" id="physical-guide" role="tabpanel">
                                <p class="text-secondary small mb-3">
                                    Pendaftaran HP fisik dilakukan saat kondisi <strong>setelan pabrik (Out-of-Box / Factory Reset)</strong>:
                                </p>
                                <div class="d-flex flex-column gap-3">
                                    <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                        <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">1</span>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1 small">Layar Awal ("Welcome")</h6>
                                            <p class="text-secondary mb-0" style="font-size: 12px;">
                                                Nyalakan HP baru di layar pembuka "Welcome" / pemilihan bahasa. Jangan lewati ke pengaturan akun.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                        <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">2</span>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1 small">Ketuk Layar 6 Kali Berturut-turut</h6>
                                            <p class="text-secondary mb-0" style="font-size: 12px;">
                                                Ketuk 6 kali berturut-turut di area kosong layar manapun. Android OS akan otomatis membuka kamera QR Scanner pembawaan sistem.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                        <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold fs-6">3</span>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1 small">Scan & Selesai</h6>
                                            <p class="text-secondary mb-0" style="font-size: 12px;">
                                                Arahkan kamera ke QR Code. Perangkat akan mendownload DPC dan terkonfigurasi secara otomatis ke server MDM.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emulator ADB Guide -->
                            <div class="tab-pane fade" id="emulator-guide" role="tabpanel">
                                <p class="text-secondary small mb-3">
                                    Untuk pengujian di Android Studio Emulator <strong>tanpa kamera fisik</strong>:
                                </p>
                                <div class="bg-white p-3 rounded-3 border mb-3">
                                    <span class="text-muted small d-block mb-1" style="font-size: 11px;">Langkah 1: Pasang DPC APK ke Emulator</span>
                                    <div class="bg-dark text-light p-2 rounded font-mono small user-select-all mb-2" style="font-size: 11px;">
                                        adb install app-debug.apk
                                    </div>

                                    <span class="text-muted small d-block mb-1" style="font-size: 11px;">Langkah 2: Jadikan DPC Sebagai Device Owner</span>
                                    <div class="bg-dark text-light p-2 rounded font-mono small user-select-all mb-2" style="font-size: 11px;">
                                        adb shell dpm set-device-owner com.mdm.dpc/.receivers.MdmAdminReceiver
                                    </div>

                                    <span class="text-muted small d-block mb-1" style="font-size: 11px;">Langkah 3: Buka aplikasi DPC lalu masukkan Token Pendaftaran</span>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px;">
                                        Masukkan kode token pendaftaran yang digenerate di dashboard atau tembak via REST API langsung.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist Note -->
                    <div class="alert alert-light border rounded-3 p-3 mb-0 d-flex align-items-center gap-3 mt-4">
                        <i class="bi bi-lightning-charge-fill text-warning fs-5 flex-shrink-0"></i>
                        <div class="small text-secondary">
                            <strong>Tips Operasional:</strong> Pastikan baterai minimal <strong>50%</strong> dan perangkat memiliki akses jaringan ke server dashboard.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Token History Table -->
        <div class="mt-5 pt-3 border-top">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-teal"></i>
                        <span>Riwayat Token Pendaftaran Standalone</span>
                    </h6>
                    <span class="text-muted" style="font-size: 11.5px;">Daftar token pendaftaran mandiri yang telah diterbitkan</span>
                </div>
            </div>

            @if($recentTokens->isEmpty())
                <div class="p-4 bg-light rounded-3 text-center border">
                    <i class="bi bi-ticket-perforated text-muted fs-2 mb-2 d-block"></i>
                    <p class="text-muted small mb-0">Belum ada token pendaftaran mandiri yang digenerate. Buat token pertama Anda melalui form di atas.</p>
                </div>
            @else
                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-secondary small">
                                <th class="py-2 px-3">Kode Token</th>
                                <th class="py-2 px-3">Cabang & Kebijakan</th>
                                <th class="py-2 px-3">Calon Pemilik</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3">Kedaluwarsa</th>
                                <th class="py-2 px-3">Dibuat Oleh</th>
                                <th class="py-2 px-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($recentTokens as $t)
                            <tr>
                                <td class="px-3 font-mono fw-bold text-dark">
                                    {{ $t->token }}
                                    @if($t->is_one_time)
                                        <span class="badge bg-light text-secondary border ms-1" style="font-size: 10px;">Single-Use</span>
                                    @else
                                        <span class="badge bg-light text-secondary border ms-1" style="font-size: 10px;">Massal</span>
                                    @endif
                                </td>
                                <td class="px-3">
                                    <div class="fw-semibold text-dark">{{ $t->branch?->name ?? 'Pusat' }}</div>
                                    <span class="text-muted font-mono" style="font-size: 11px;">{{ $t->policy_id ?: 'Mode_kiosk' }}</span>
                                </td>
                                <td class="px-3">
                                    @if(!empty($t->owner_data['nama']))
                                        <div class="fw-semibold text-dark">{{ $t->owner_data['nama'] }}</div>
                                        <span class="text-muted font-mono" style="font-size: 11px;">ID: {{ $t->owner_data['id_anggota'] ?? '-' }}</span>
                                    @else
                                        <span class="text-muted">— Belum dialokasikan —</span>
                                    @endif
                                </td>
                                <td class="px-3">
                                    @if($t->is_used)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i>Sudah Digunakan
                                        </span>
                                    @elseif($t->expires_at->isPast())
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="bi bi-x-circle me-1"></i>Kedaluwarsa
                                        </span>
                                    @else
                                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
                                            <i class="bi bi-check2-circle me-1"></i>Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 text-secondary font-mono" style="font-size: 11.5px;">
                                    {{ $t->expires_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-3 text-secondary">
                                    {{ $t->creator?->name ?? 'System' }}
                                </td>
                                <td class="px-3 text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('enrollment.tokens.show', $t->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" title="Lihat / Cetak QR Code">
                                            <i class="bi bi-qr-code"></i>
                                            <span>Lihat QR</span>
                                        </a>
                                        @if(!$t->is_used && $t->expires_at->isFuture())
                                        <form action="{{ route('enrollment.tokens.revoke', $t->id) }}" method="POST" onsubmit="return confirm('Batalkan token pendaftaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan Token">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
