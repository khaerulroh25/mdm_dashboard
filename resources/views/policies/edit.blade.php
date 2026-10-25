@extends('layouts.app')

@section('title', 'Konfigurasi Policy: ' . $policy->name)
@section('page_title', 'Konfigurasi Kebijakan (Policy)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('policies.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Daftar">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">{{ $policy->name }}</h4>
            <span class="font-mono text-teal small">ID: {{ $policy->policy_id }}</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Restriction Settings -->
    <div class="col-12 col-lg-7">
        <div class="card card-mdm border-0 h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-sliders text-teal me-2"></i>Pengaturan Pembatasan (Restrictions)
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('policies.update', $policy->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-secondary">Nama Policy</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $policy->name) }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label small fw-semibold text-secondary">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $policy->description) }}</textarea>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Kontrol Fitur & Keamanan</h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="camera_disabled" value="1" id="camera_disabled" {{ $policy->camera_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="camera_disabled">
                                    Nonaktifkan Kamera
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="factory_reset_disabled" value="1" id="factory_reset_disabled" {{ $policy->factory_reset_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="factory_reset_disabled">
                                    Cegah Factory Reset
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="wifi_config_disabled" value="1" id="wifi_config_disabled" {{ $policy->wifi_config_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="wifi_config_disabled">
                                    Kunci Pengaturan Wi-Fi
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="safe_boot_disabled" value="1" id="safe_boot_disabled" {{ $policy->safe_boot_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="safe_boot_disabled">
                                    Nonaktifkan Safe Boot
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="install_apps_disabled" value="1" id="install_apps_disabled" {{ $policy->install_apps_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="install_apps_disabled">
                                    Kunci Instalasi Luar
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_untrusted_apps" value="1" id="allow_untrusted_apps" {{ $policy->allow_untrusted_apps ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="allow_untrusted_apps">
                                    Izinkan Instalasi APK Luar (Sideloading)
                                </label>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                Izinkan pengguna memasang APK di luar Play Store (misal: APK ujian/CBT).
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="location_mode" class="form-label small fw-semibold text-dark">Mode Lokasi (GPS)</label>
                            <select name="location_mode" id="location_mode" class="form-select form-select-sm">
                                <option value="">Default Pengguna</option>
                                <option value="HIGH_ACCURACY" {{ $policy->location_mode == 'HIGH_ACCURACY' ? 'selected' : '' }}>Paksa HIGH_ACCURACY</option>
                                <option value="BATTERY_SAVING" {{ $policy->location_mode == 'BATTERY_SAVING' ? 'selected' : '' }}>BATTERY_SAVING</option>
                                <option value="OFF" {{ $policy->location_mode == 'OFF' ? 'selected' : '' }}>OFF</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="play_store_mode" class="form-label small fw-semibold text-dark">
                                <i class="bi bi-google-play text-teal me-1"></i>Mode Toko Aplikasi (Google Play Store)
                            </label>
                            <select name="play_store_mode" id="play_store_mode" class="form-select form-select-sm">
                                <option value="BLACKLIST" {{ ($policy->play_store_mode ?? 'WHITELIST') == 'BLACKLIST' ? 'selected' : '' }}>
                                    Buka Penuh Google Play Publik (BLACKLIST) — Rekomendasi Pelanggan Cicilan
                                </option>
                                <option value="WHITELIST" {{ ($policy->play_store_mode ?? 'WHITELIST') == 'WHITELIST' ? 'selected' : '' }}>
                                    Batasi Hanya Aplikasi Tertentu (WHITELIST) — Mode Kiosk / Enterprise
                                </option>
                            </select>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Buka Penuh (BLACKLIST):</strong> Pelanggan bisa login akun Gmail sendiri dan bebas mengunduh aplikasi publik (WhatsApp, TikTok, YouTube, Mobile Banking, dll).
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-danger border-bottom pb-2 mb-3 mt-4 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-danger"></i>
                        <span>Proteksi Kiosk & Lock Screen (Gagal Bayar / Lost Mode)</span>
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="kiosk_status_bar_disabled" value="1" id="kiosk_status_bar_disabled" {{ $policy->kiosk_status_bar_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="kiosk_status_bar_disabled">
                                    Kunci Bilah Notifikasi Atas (Status Bar)
                                </label>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                Mencegah pengguna menarik layar dari atas ke bawah (swipe-down) untuk melihat notifikasi atau quick settings.
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="kiosk_settings_blocked" value="1" id="kiosk_settings_blocked" {{ $policy->kiosk_settings_blocked ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="kiosk_settings_blocked">
                                    Blokir Akses Menu Setelan (Settings HP)
                                </label>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                Memblokir akses ke aplikasi Pengaturan / Settings HP dalam mode kiosk.
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="kiosk_navigation_disabled" value="1" id="kiosk_navigation_disabled" {{ $policy->kiosk_navigation_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="kiosk_navigation_disabled">
                                    Matikan Tombol Navigasi Sistem
                                </label>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                Menonaktifkan tombol Home, Back, dan Recent Apps bawaan Android.
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="screen_capture_disabled" value="1" id="screen_capture_disabled" {{ $policy->screen_capture_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="screen_capture_disabled">
                                    Cegah Tangkapan Layar (Screenshot)
                                </label>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                Memblokir tangkapan layar (screenshot) dan perekaman layar HP.
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="keyguard_disabled" value="1" id="keyguard_disabled" {{ $policy->keyguard_disabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="keyguard_disabled">
                                    Bypass Layar Kunci Ganda (Keyguard)
                                </label>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                Melewati lock screen bawaan HP sehingga langsung menampilkan layar kiosk saat tombol daya ditekan.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end border-top pt-3">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-save me-1"></i> Perbarui Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Installed Apps in this Policy -->
    <div class="col-12 col-lg-5">
        <div class="card card-mdm border-0 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-google-play text-teal me-2"></i>Aplikasi Terpasang (Whitelist)
                </h6>
                <button type="button" class="btn btn-sm btn-teal" data-bs-toggle="modal" data-bs-target="#addAppModal">
                    <i class="bi bi-plus-circle me-1"></i>Tambah App
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-tosca">
                            <tr>
                                <th scope="col" class="ps-4">Aplikasi</th>
                                <th scope="col">Tipe Instalasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($policy->applications as $app)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $app->app_name }}</div>
                                    <span class="package-name text-muted">{{ $app->package_name }}</span>
                                </td>
                                <td>
                                    @if($app->pivot->install_type === 'FORCE_INSTALLED')
                                        <span class="badge badge-compliant">FORCE INSTALLED</span>
                                    @elseif($app->pivot->install_type === 'KIOSK')
                                        <span class="badge badge-warning-custom">KIOSK APP</span>
                                    @else
                                        <span class="badge badge-pending-custom">{{ $app->pivot->install_type }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted small">
                                    Belum ada aplikasi yang ditetapkan khusus untuk policy ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add App to Policy -->
<div class="modal fade" id="addAppModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('policies.add-app', $policy->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Pasang Aplikasi ke Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Package Name -->
                    <div class="mb-3">
                        <label for="package_name" class="form-label small fw-semibold">Pilih dari Katalog atau Ketik Package Name</label>
                        <input type="text" name="package_name" id="package_name" list="catalogApps" class="form-control font-mono" placeholder="Contoh: com.android.chrome" required>
                        <datalist id="catalogApps">
                            @foreach($allApps as $catApp)
                            <option value="{{ $catApp->package_name }}">{{ $catApp->app_name }}</option>
                            @endforeach
                        </datalist>
                    </div>

                    <!-- Install Type -->
                    <div class="mb-3">
                        <label for="install_type" class="form-label small fw-semibold">Tipe Instalasi</label>
                        <select name="install_type" id="install_type" class="form-select" required>
                            <option value="AVAILABLE">AVAILABLE (Tersedia di Play Store perangkat)</option>
                            <option value="FORCE_INSTALLED" selected>FORCE_INSTALLED (Terpasang otomatis tanpa interaksi user)</option>
                            <option value="KIOSK">KIOSK (Aplikasi utama layar terkunci)</option>
                            <option value="BLOCKED">BLOCKED (Dilarang dipasang)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Tambahkan Aplikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
