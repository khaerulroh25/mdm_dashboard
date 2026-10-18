@extends('layouts.app')

@section('title', 'Buat Policy Baru')
@section('page_title', 'Buat Kebijakan Enterprise Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card card-mdm border-0">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">Formulir Kebijakan (Policy)</h5>
                <span class="text-secondary small">Aturan ini akan langsung didaftarkan ke Google Cloud AMAPI</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('policies.store') }}" method="POST">
                    @csrf

                    <!-- Policy ID -->
                    <div class="mb-3">
                        <label for="policy_id" class="form-label small fw-semibold text-secondary">Policy ID (Identifier Unik AMAPI)</label>
                        <input type="text" name="policy_id" id="policy_id" class="form-control font-mono @error('policy_id') is-invalid @enderror" placeholder="Contoh: policy_sales, kiosk_mode" value="{{ old('policy_id') }}" required>
                        <div class="form-text small">Hanya boleh huruf, angka, garis bawah (_), dan tanda hubung (-). Tidak boleh spasi.</div>
                        @error('policy_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Display Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-secondary">Nama Tampilan</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Kebijakan Khusus Agen Lapangan" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label small fw-semibold text-secondary">Deskripsi Kebijakan</label>
                        <textarea name="description" id="description" class="form-control" rows="2" placeholder="Catatan tujuan penerapan policy ini...">{{ old('description') }}</textarea>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-shield-lock text-teal me-2"></i>Pengaturan Pembatasan Sistem (Restrictions)
                    </h6>

                    <!-- Checkboxes -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="camera_disabled" value="1" id="camera_disabled" {{ old('camera_disabled') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="camera_disabled">
                                    Nonaktifkan Kamera (Camera Disabled)
                                </label>
                                <div class="form-text" style="font-size: 11px;">Mencegah penggunaan kamera di seluruh aplikasi.</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="factory_reset_disabled" value="1" id="factory_reset_disabled" {{ old('factory_reset_disabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="factory_reset_disabled">
                                    Cegah Factory Reset (Disarankan)
                                </label>
                                <div class="form-text" style="font-size: 11px;">Mencegah pengguna mereset perangkat ke setelan pabrik.</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="wifi_config_disabled" value="1" id="wifi_config_disabled" {{ old('wifi_config_disabled') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="wifi_config_disabled">
                                    Kunci Pengaturan Wi-Fi
                                </label>
                                <div class="form-text" style="font-size: 11px;">Mencegah penambahan/modifikasi jaringan Wi-Fi baru.</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="safe_boot_disabled" value="1" id="safe_boot_disabled" {{ old('safe_boot_disabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="safe_boot_disabled">
                                    Nonaktifkan Safe Boot
                                </label>
                                <div class="form-text" style="font-size: 11px;">Mencegah boot ke mode aman Android.</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="install_apps_disabled" value="1" id="install_apps_disabled" {{ old('install_apps_disabled') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-dark" for="install_apps_disabled">
                                    Kunci Instalasi Aplikasi Luar
                                </label>
                                <div class="form-text" style="font-size: 11px;">Hanya izinkan aplikasi dari katalog resmi.</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="location_mode" class="form-label small fw-semibold text-dark">Mode Lokasi (GPS)</label>
                            <select name="location_mode" id="location_mode" class="form-select form-select-sm">
                                <option value="">Default (Sesuai Pengguna)</option>
                                <option value="HIGH_ACCURACY" {{ old('location_mode') == 'HIGH_ACCURACY' ? 'selected' : '' }}>Paksa Akurasi Tinggi (HIGH_ACCURACY)</option>
                                <option value="BATTERY_SAVING" {{ old('location_mode') == 'BATTERY_SAVING' ? 'selected' : '' }}>Hemat Baterai (BATTERY_SAVING)</option>
                                <option value="OFF" {{ old('location_mode') == 'OFF' ? 'selected' : '' }}>Matikan Lokasi (OFF)</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('policies.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Simpan & Kirim ke Google AMAPI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
