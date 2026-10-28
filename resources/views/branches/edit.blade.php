@extends('layouts.app')

@section('title', 'Edit Cabang: ' . $branch->name)
@section('page_title', 'Manajemen Master Cabang')

@section('content')
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Edit Master Cabang: <span class="text-teal font-mono">{{ $branch->code }}</span></h5>
            <span class="text-secondary small">Perbarui informasi cabang, nomor kontak darurat Lost Mode, dan status operasional</span>
        </div>
        <div>
            <a href="{{ route('branches.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar Cabang</span>
            </a>
        </div>
    </div>

    <form action="{{ route('branches.update', $branch->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left Column: Form Edit Cabang -->
                <div class="col-12 col-lg-7">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square text-teal fs-5"></i>
                            <span>Perbarui Identitas & Kontak Cabang</span>
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="name" class="form-label small fw-semibold text-secondary">
                                    Nama Cabang <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $branch->name) }}" required>
                                @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">
                                    Kode Cabang (Otomatis)
                                </label>
                                <input type="text" class="form-control bg-light font-mono fw-bold text-teal" value="{{ $branch->code }}" readonly disabled>
                                <div class="form-text" style="font-size: 11px;">Kode permanen sistem.</div>
                            </div>

                            <div class="col-12">
                                <label for="contact_phone" class="form-label small fw-semibold text-secondary">
                                    Nomor Telepon Admin Cabang (Opsional)
                                </label>
                                <input type="text" name="contact_phone" id="contact_phone"
                                    class="form-control font-mono @error('contact_phone') is-invalid @enderror"
                                    value="{{ old('contact_phone', $branch->contact_phone) }}"
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
                                    placeholder="Masukkan alamat kantor cabang atau keterangan wilayah operasional">{{ old('address', $branch->address) }}</textarea>
                                @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="enterprise_id" class="form-label small fw-semibold text-secondary">
                                    Alokasi Akun Enterprise Google Cloud
                                </label>
                                <select name="enterprise_id" id="enterprise_id" class="form-select @error('enterprise_id') is-invalid @enderror">
                                    <option value="">-- Gunakan Enterprise Default (Aktif) --</option>
                                    @foreach($enterprises as $ent)
                                        <option value="{{ $ent->id }}" {{ old('enterprise_id', $branch->enterprise_id) == $ent->id ? 'selected' : '' }}>
                                            {{ $ent->name }} ({{ $ent->enterprise_id }}) {{ $ent->is_active ? '★ DEFAULT' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text" style="font-size: 11px;">
                                    Enterprise yang digunakan saat pendaftaran QR perangkat di cabang ini.
                                </div>
                                @error('enterprise_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="default_policy_id" class="form-label small fw-semibold text-secondary">
                                    Kebijakan Default Pendaftaran
                                </label>
                                <select name="default_policy_id" id="default_policy_id" class="form-select @error('default_policy_id') is-invalid @enderror">
                                    @foreach($policies as $pol)
                                        <option value="{{ $pol->policy_id }}" {{ old('default_policy_id', $branch->default_policy_id ?? 'Mode_kiosk') == $pol->policy_id ? 'selected' : '' }}>
                                            {{ $pol->name }} ({{ $pol->policy_id }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text" style="font-size: 11px;">
                                    Kebijakan yang otomatis diterapkan saat scan QR pendaftaran cabang.
                                </div>
                                @error('default_policy_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="bg-white p-3 rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
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

                <!-- Right Column: Ringkasan Statistik Cabang -->
                <div class="col-12 col-lg-5">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-bar-chart-line text-teal fs-5"></i>
                                <span>Ringkasan Keterkaitan Cabang</span>
                            </h6>

                            <div class="bg-white p-3 rounded-3 border mb-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-secondary small d-block">Perangkat Aktif</span>
                                    <h4 class="fw-bold text-dark mb-0">{{ $branch->active_devices_count }} <small class="fs-6 fw-normal text-muted">/ {{ $branch->devices_count }} total</small></h4>
                                </div>
                                <a href="{{ route('devices.index', ['branch_id' => $branch->id]) }}" class="btn btn-sm btn-outline-teal">
                                    Lihat Perangkat
                                </a>
                            </div>

                            <div class="bg-white p-3 rounded-3 border mb-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-secondary small d-block">Akun Admin / Operator Cabang</span>
                                    <h4 class="fw-bold text-dark mb-0">{{ $branch->users_count }} <small class="fs-6 fw-normal text-muted">Pengguna</small></h4>
                                </div>
                                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                                    Kelola User
                                </a>
                            </div>
                        </div>

                        <div class="alert alert-light border rounded-3 p-3 mt-3 mb-0 d-flex align-items-center gap-3">
                            <i class="bi bi-clock-history text-teal fs-5 flex-shrink-0"></i>
                            <div class="small text-secondary">
                                Terdaftar sejak <strong>{{ $branch->created_at->format('d M Y, H:i') }} WIB</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3 px-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-secondary small">
                Terakhir diperbarui: <strong>{{ $branch->updated_at->format('d M Y, H:i') }} WIB</strong>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary btn-sm px-3">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm px-4 d-flex align-items-center gap-2">
                    <i class="bi bi-save"></i>
                    <span>Perbarui Cabang</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
