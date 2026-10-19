@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page_title', 'Manajemen Pengguna Dashboard')

@section('content')
<!-- Harmonized User Create Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <!-- Header with Back Button -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Tambah Pengguna Baru</h5>
            <span class="text-secondary small">Buat akun untuk administrator, operator lapangan, atau auditor sistem</span>
        </div>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <form action="{{ route('users.store') }}" method="POST" id="createUserForm">
        @csrf
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left Column: Form Kredensial & Profil Pengguna -->
                <div class="col-12 col-lg-7">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus-fill text-teal fs-5"></i>
                            <span>Informasi Akun & Kredensial</span>
                        </h6>

                        <div class="row g-3">
                            <!-- Username -->
                            <div class="col-12 col-md-6">
                                <label for="username" class="form-label small fw-semibold text-secondary">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="username" id="username" 
                                    class="form-control font-mono @error('username') is-invalid @enderror" 
                                    value="{{ old('username') }}" 
                                    placeholder="Contoh: operator_jkt" required autofocus>
                                <div class="form-text" style="font-size: 11px;">Hanya huruf, angka, garis bawah (_), dan tanda hubung (-).</div>
                                @error('username')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Full Name -->
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label small fw-semibold text-secondary">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name') }}" 
                                    placeholder="Contoh: Budi Santoso" required>
                                @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label small fw-semibold text-secondary">
                                    Alamat Email (Opsional)
                                </label>
                                <input type="email" name="email" id="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" 
                                    placeholder="nama@perusahaan.com">
                                @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Initial Password -->
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label small fw-semibold text-secondary">
                                    Password Awal <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" id="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    placeholder="Minimal 6 karakter" required>
                                @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Role -->
                            <div class="col-12">
                                <label for="role" class="form-label small fw-semibold text-secondary">
                                    Peran & Hak Akses (Role) <span class="text-danger">*</span>
                                </label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>
                                        Operator Lapangan — Kelola Perangkat, Remote Command, Enrollment
                                    </option>
                                    <option value="auditor" {{ old('role') == 'auditor' ? 'selected' : '' }}>
                                        Auditor / Viewer — Hanya Melihat Data & Riwayat Audit Logs
                                    </option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                                        Super Administrator — Akses Penuh ke Seluruh Sistem
                                    </option>
                                </select>
                                @error('role')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Matriks Peran & Panduan Keamanan (RBAC Guide) -->
                <div class="col-12 col-lg-5">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-teal fs-5"></i>
                                <span>Panduan Hak Akses Pengguna (RBAC)</span>
                            </h6>

                            <div class="d-flex flex-column gap-3 mb-4">
                                <!-- Role: Operator -->
                                <div class="bg-white p-3 rounded-3 border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                                            <i class="bi bi-person-gear text-teal"></i>
                                            <span>Operator Lapangan</span>
                                        </div>
                                        <span class="badge badge-compliant">OPERATOR</span>
                                    </div>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.4;">
                                        Dapat memonitor armada perangkat, mengeksekusi perintah jarak jauh (Lock/Reboot/Wipe), menerapkan kebijakan, dan membuat QR Code enrollment.
                                    </p>
                                </div>

                                <!-- Role: Auditor -->
                                <div class="bg-white p-3 rounded-3 border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                                            <i class="bi bi-eye text-primary"></i>
                                            <span>Auditor / Viewer</span>
                                        </div>
                                        <span class="badge bg-light text-secondary border">AUDITOR</span>
                                    </div>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.4;">
                                        Hak akses baca (<em>read-only</em>). Dapat melihat seluruh data armada, status kepatuhan, dan catatan log aktivitas sistem tanpa hak eksekusi.
                                    </p>
                                </div>

                                <!-- Role: Superadmin -->
                                <div class="bg-white p-3 rounded-3 border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                                            <i class="bi bi-shield-shaded text-danger"></i>
                                            <span>Super Administrator</span>
                                        </div>
                                        <span class="badge badge-lost-mode">ADMIN</span>
                                    </div>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.4;">
                                        Otoritas tertinggi. Dapat mengelola akun staf, menghapus data sistem, konfigurasi Google AMAPI, dan seluruh wewenang operator & auditor.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Security Note -->
                        <div class="alert alert-light border rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                            <i class="bi bi-shield-lock-fill text-teal fs-5 flex-shrink-0"></i>
                            <div class="small text-secondary">
                                <strong>Keamanan Akun:</strong> Pastikan password awal terdiri dari kombinasi huruf dan angka serta tidak dibagikan antar personil demi validitas audit log.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="card-footer bg-white py-3 px-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 text-secondary small">
                <i class="bi bi-clock-history text-teal fs-6"></i>
                <span>Aktivitas penambahan akun pengguna akan otomatis tercatat di Audit Trail.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm px-3">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm px-4 d-flex align-items-center gap-2">
                    <i class="bi bi-save"></i>
                    <span>Simpan Pengguna</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
