@extends('layouts.app')

@section('title', 'Tambah Cabang Baru')
@section('page_title', 'Manajemen Master Cabang')

@section('content')
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Tambah Cabang Operasional Baru</h5>
            <span class="text-secondary small">Daftarkan cabang baru untuk memisahkan pengelolaan perangkat dan akun admin cabang</span>
        </div>
        <div>
            <a href="{{ route('branches.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar Cabang</span>
            </a>
        </div>
    </div>

    <form action="{{ route('branches.store') }}" method="POST">
        @csrf
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left Column: Form Input Cabang -->
                <div class="col-12 col-lg-7">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-building-add text-teal fs-5"></i>
                            <span>Identitas & Kontak Cabang</span>
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="name" class="form-label small fw-semibold text-secondary">
                                    Nama Cabang <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="Contoh: Cabang Karawang Barat" required autofocus>
                                @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">
                                    Kode Cabang (Otomatis)
                                </label>
                                <div class="form-control bg-white font-mono fw-bold text-teal d-flex align-items-center justify-content-between" style="border-style: dashed;">
                                    <span id="codePreview">AUTO</span>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle" style="font-size: 10px;">AUTO</span>
                                </div>
                                <div class="form-text" style="font-size: 11px;">Dibuat otomatis dari nama cabang.</div>
                            </div>

                            <div class="col-12">
                                <label for="contact_phone" class="form-label small fw-semibold text-secondary">
                                    Nomor Telepon Admin Cabang (Opsional)
                                </label>
                                <input type="text" name="contact_phone" id="contact_phone"
                                    class="form-control font-mono @error('contact_phone') is-invalid @enderror"
                                    value="{{ old('contact_phone') }}"
                                    placeholder="Contoh: 0858-1406-4274">
                                <div class="form-text" style="font-size: 11px;">
                                    Ditampilkan otomatis pada tombol panggilan darurat saat perangkat nasabah di cabang ini dikunci dengan <strong>Mode Gagal Bayar (Lock Screen Kiosk)</strong>.
                                </div>
                                @error('contact_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label small fw-semibold text-secondary">
                                    Alamat / Keterangan Wilayah (Opsional)
                                </label>
                                <textarea name="address" id="address" rows="3"
                                    class="form-control @error('address') is-invalid @enderror"
                                    placeholder="Masukkan alamat kantor cabang atau keterangan wilayah operasional">{{ old('address') }}</textarea>
                                @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="bg-white p-3 rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold small text-dark" for="is_active">
                                            Status Cabang Aktif
                                        </label>
                                    </div>
                                    <div class="text-secondary mt-1" style="font-size: 11px;">
                                        Cabang yang aktif akan muncul pada pilihan pendaftaran QR Code (Enrollment) dan pembuatan akun pengguna.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Panduan Penggunaan Multi-Cabang -->
                <div class="col-12 col-lg-5">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-diagram-3 text-teal fs-5"></i>
                                <span>Cara Kerja Isolasi Multi-Cabang</span>
                            </h6>

                            <div class="d-flex flex-column gap-3">
                                <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                    <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold">1</span>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 small">Buat Master Cabang</h6>
                                        <p class="text-secondary mb-0" style="font-size: 12px;">
                                            Cukup ketik nama cabang (misal: <code>Cabang Karawang Barat</code>), kode unik cabang akan di-generate secara otomatis oleh sistem.
                                        </p>
                                    </div>
                                </div>

                                <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                    <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold">2</span>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 small">Tautkan Akun Admin/Operator Cabang</h6>
                                        <p class="text-secondary mb-0" style="font-size: 12px;">
                                            Pada menu <strong>Manage Users</strong>, pilih penempatan cabang untuk akun tersebut agar ia hanya dapat melihat dan mengelola perangkat cabangnya sendiri.
                                        </p>
                                    </div>
                                </div>

                                <div class="bg-white p-3 rounded-3 border d-flex gap-3 align-items-start">
                                    <span class="badge bg-teal text-white rounded-circle p-2 px-3 fw-bold">3</span>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 small">Otomatisasi Scan QR Enrollment</h6>
                                        <p class="text-secondary mb-0" style="font-size: 12px;">
                                            Saat Admin Cabang membuat QR Enrollment, token Google AMAPI otomatis menyisipkan identitas cabang sehingga HP baru langsung masuk ke cabang tersebut saat disinkronkan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border rounded-3 p-3 mt-4 mb-0 d-flex align-items-center gap-3">
                            <i class="bi bi-shield-check text-teal fs-5 flex-shrink-0"></i>
                            <div class="small text-secondary">
                                <strong>Superadmin Pusat:</strong> Tetap dapat memantau seluruh cabang sekaligus dan memindahkan perangkat antar cabang kapan saja.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3 px-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-secondary small">
                <i class="bi bi-magic text-teal me-1"></i> Kode cabang dibuat dan dijamin unik secara otomatis oleh sistem.
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary btn-sm px-3">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm px-4 d-flex align-items-center gap-2">
                    <i class="bi bi-save"></i>
                    <span>Simpan Cabang</span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const codePreview = document.getElementById('codePreview');

    function computePreview(rawName) {
        let cleaned = rawName.trim().replace(/^(cabang|kantor|unit|wilayah|area)\s+/i, '');
        cleaned = cleaned.replace(/[^a-zA-Z0-9\s]/g, ' ').trim().toUpperCase();
        const words = cleaned.split(/\s+/).filter(Boolean);

        if (words.length === 0) return 'AUTO';
        if (words.length === 1) {
            const w = words[0];
            const cons = w[0] + w.slice(1).replace(/[AEIOU]/g, '');
            return (cons.length >= 3 ? cons.slice(0, 3) : w.slice(0, 3)) || 'CBG';
        }
        const w1 = words[0];
        const cons1 = w1[0] + w1.slice(1).replace(/[AEIOU]/g, '');
        const part1 = cons1.length >= 3 ? cons1.slice(0, 3) : w1.slice(0, 3);
        const initials = words.slice(1).map(w => w[0]).join('').slice(0, 3);
        return `${part1}-${initials}`;
    }

    nameInput.addEventListener('input', function () {
        codePreview.textContent = computePreview(this.value);
    });
});
</script>
@endpush
@endsection
