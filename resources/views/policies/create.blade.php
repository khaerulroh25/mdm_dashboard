@extends('layouts.app')

@section('title', 'Buat Policy Baru')
@section('page_title', 'Kebijakan Keamanan (Policies)')

@section('content')
<!-- Policy Create Card - Harmonized with Policies Dashboard -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <!-- Card Header matching policies/index.blade.php -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Buat Kebijakan (Policy) Baru</h5>
            <span class="text-secondary small">Konfigurasi aturan pembatasan, kontrol sistem, dan mode Google Play untuk perangkat</span>
        </div>
        <div>
            <a href="{{ route('policies.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <form action="{{ route('policies.store') }}" method="POST">
        @csrf
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left Column: Identitas & Informasi Kebijakan -->
                <div class="col-12 col-lg-5">
                    <div class="p-3 bg-light bg-opacity-50 rounded-3 border mb-4">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-teal"></i>
                            <span>Informasi Dasar Kebijakan</span>
                        </h6>

                        <!-- Policy ID -->
                        <div class="mb-3">
                            <label for="policy_id" class="form-label small fw-semibold text-secondary">Policy ID (Identifier Unik AMAPI) <span class="text-danger">*</span></label>
                            <input type="text" name="policy_id" id="policy_id" class="form-control font-mono @error('policy_id') is-invalid @enderror" placeholder="Contoh: policy_sales, kiosk_mode" value="{{ old('policy_id') }}" required>
                            <div class="form-text" style="font-size: 11px;">Hanya huruf, angka, garis bawah (_), dan tanda hubung (-). Tanpa spasi.</div>
                            @error('policy_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Display Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-secondary">Nama Tampilan Kebijakan <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Kebijakan Khusus Agen Lapangan" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-0">
                            <label for="description" class="form-label small fw-semibold text-secondary">Deskripsi Kebijakan</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Catatan tujuan atau spesifikasi penerapan policy ini...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Mode Play Store & GPS Card -->
                    <div class="p-3 bg-light bg-opacity-50 rounded-3 border">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-google-play text-teal"></i>
                            <span>Akses Google Play & Lokasi</span>
                        </h6>

                        <!-- Play Store Mode -->
                        <div class="mb-3">
                            <label for="play_store_mode" class="form-label small fw-semibold text-dark">Mode Toko Aplikasi (Google Play Store)</label>
                            <select name="play_store_mode" id="play_store_mode" class="form-select form-select-sm">
                                <option value="BLACKLIST" {{ old('play_store_mode', 'BLACKLIST') == 'BLACKLIST' ? 'selected' : '' }}>
                                    Buka Penuh Google Play Publik (BLACKLIST) — Rekomendasi
                                </option>
                                <option value="WHITELIST" {{ old('play_store_mode') == 'WHITELIST' ? 'selected' : '' }}>
                                    Batasi Hanya Aplikasi Tertentu (WHITELIST) — Mode Kiosk
                                </option>
                            </select>
                            <div class="form-text" style="font-size: 11px; line-height: 1.4;">
                                <strong>BLACKLIST:</strong> Pengguna bebas mengunduh aplikasi publik dari Google Play Store.
                            </div>
                        </div>

                        <!-- Location Mode -->
                        <div class="mb-0">
                            <label for="location_mode" class="form-label small fw-semibold text-dark">Mode Lokasi (GPS Perangkat)</label>
                            <select name="location_mode" id="location_mode" class="form-select form-select-sm">
                                <option value="">Default (Sesuai Pengaturan Pengguna)</option>
                                <option value="HIGH_ACCURACY" {{ old('location_mode') == 'HIGH_ACCURACY' ? 'selected' : '' }}>Paksa Akurasi Tinggi (HIGH_ACCURACY)</option>
                                <option value="BATTERY_SAVING" {{ old('location_mode') == 'BATTERY_SAVING' ? 'selected' : '' }}>Hemat Baterai (BATTERY_SAVING)</option>
                                <option value="OFF" {{ old('location_mode') == 'OFF' ? 'selected' : '' }}>Matikan Lokasi (OFF)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Pembatasan Sistem (Restrictions Grid) -->
                <div class="col-12 col-lg-7">
                    <div class="p-3 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-teal"></i>
                            <span>Pengaturan Pembatasan Sistem (Device Restrictions)</span>
                        </h6>

                        <div class="row g-3 flex-grow-1">
                            <!-- Factory Reset -->
                            <div class="col-12 col-md-6">
                                <div class="bg-white p-3 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="factory_reset_disabled" value="1" id="factory_reset_disabled" {{ old('factory_reset_disabled', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small text-dark" for="factory_reset_disabled">
                                            Cegah Factory Reset
                                        </label>
                                    </div>
                                    <div class="text-secondary" style="font-size: 11px;">Mencegah pengguna mereset perangkat ke setelan pabrik. <strong>(Sangat disarankan)</strong></div>
                                </div>
                            </div>

                            <!-- Safe Boot -->
                            <div class="col-12 col-md-6">
                                <div class="bg-white p-3 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="safe_boot_disabled" value="1" id="safe_boot_disabled" {{ old('safe_boot_disabled', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small text-dark" for="safe_boot_disabled">
                                            Nonaktifkan Safe Boot
                                        </label>
                                    </div>
                                    <div class="text-secondary" style="font-size: 11px;">Mencegah pengguna menyalakan perangkat dalam mode aman Android untuk membypass MDM.</div>
                                </div>
                            </div>

                            <!-- Camera -->
                            <div class="col-12 col-md-6">
                                <div class="bg-white p-3 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="camera_disabled" value="1" id="camera_disabled" {{ old('camera_disabled') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small text-dark" for="camera_disabled">
                                            Nonaktifkan Kamera
                                        </label>
                                    </div>
                                    <div class="text-secondary" style="font-size: 11px;">Memblokir akses perangkat keras kamera di seluruh aplikasi sistem.</div>
                                </div>
                            </div>

                            <!-- Wi-Fi Config -->
                            <div class="col-12 col-md-6">
                                <div class="bg-white p-3 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="wifi_config_disabled" value="1" id="wifi_config_disabled" {{ old('wifi_config_disabled') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small text-dark" for="wifi_config_disabled">
                                            Kunci Pengaturan Wi-Fi
                                        </label>
                                    </div>
                                    <div class="text-secondary" style="font-size: 11px;">Mencegah pengguna menambah atau memodifikasi sambungan jaringan Wi-Fi baru.</div>
                                </div>
                            </div>

                            <!-- Install Apps Disabled -->
                            <div class="col-12 col-md-6">
                                <div class="bg-white p-3 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="install_apps_disabled" value="1" id="install_apps_disabled" {{ old('install_apps_disabled') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small text-dark" for="install_apps_disabled">
                                            Kunci Instalasi Aplikasi Luar
                                        </label>
                                    </div>
                                    <div class="text-secondary" style="font-size: 11px;">Hanya mengizinkan aplikasi yang disediakan melalui katalog resmi MDM.</div>
                                </div>
                            </div>

                            <!-- Allow Untrusted Apps -->
                            <div class="col-12 col-md-6">
                                <div class="bg-white p-3 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="allow_untrusted_apps" value="1" id="allow_untrusted_apps" {{ old('allow_untrusted_apps', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small text-dark" for="allow_untrusted_apps">
                                            Izinkan Sideloading APK Luar
                                        </label>
                                    </div>
                                    <div class="text-secondary" style="font-size: 11px;">Pengguna dapat memasang file APK secara manual (misal: APK ujian/CBT atau aplikasi custom internal).</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="card-footer bg-white py-3 px-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 text-secondary small">
                <i class="bi bi-cloud-check text-teal fs-6"></i>
                <span>Aturan ini akan langsung disinkronkan dan didaftarkan ke Google Cloud AMAPI.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('policies.index') }}" class="btn btn-secondary btn-sm px-3">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm px-4 d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Simpan & Kirim ke Google AMAPI</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
