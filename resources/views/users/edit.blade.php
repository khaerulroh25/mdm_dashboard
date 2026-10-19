@extends('layouts.app')

@section('title', 'Edit Pengguna: ' . $user->username)
@section('page_title', 'Manajemen Pengguna Dashboard')

@section('content')
<!-- Harmonized User Edit Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <!-- Header with Back Button -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Edit Pengguna: <span class="font-mono text-teal">{{ $user->username }}</span></h5>
            <span class="text-secondary small">Perbarui data profil, peran hak akses, atau atur ulang password pengguna</span>
        </div>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <form action="{{ route('users.update', $user->id) }}" method="POST" id="editUserForm">
        @csrf
        @method('PUT')
        
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left Column: Form Kredensial & Profil Pengguna -->
                <div class="col-12 col-lg-7">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-person-gear text-teal fs-5"></i>
                            <span>Perbarui Data Profil & Kredensial</span>
                        </h6>

                        <div class="row g-3">
                            <!-- Username -->
                            <div class="col-12 col-md-6">
                                <label for="username" class="form-label small fw-semibold text-secondary">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="username" id="username" 
                                    class="form-control font-mono @error('username') is-invalid @enderror" 
                                    value="{{ old('username', $user->username) }}" required>
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
                                    value="{{ old('name', $user->name) }}" required>
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
                                    value="{{ old('email', $user->email) }}" 
                                    placeholder="nama@perusahaan.com">
                                @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password Baru -->
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label small fw-semibold text-secondary">
                                    Password Baru (Opsional)
                                </label>
                                <input type="password" name="password" id="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    placeholder="Biarkan kosong jika tidak diubah">
                                <div class="form-text" style="font-size: 11px;">Minimal 6 karakter jika ingin mengganti password lama.</div>
                                @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Role -->
                            <div class="col-12">
                                <label for="role" class="form-label small fw-semibold text-secondary">
                                    Peran & Hak Akses (Role) <span class="text-danger">*</span>
                                </label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <option value="operator" {{ old('role', $user->role) == 'operator' ? 'selected' : '' }}>
                                        Operator Lapangan — Kelola Perangkat, Remote Command, Enrollment
                                    </option>
                                    <option value="auditor" {{ old('role', $user->role) == 'auditor' ? 'selected' : '' }}>
                                        Auditor / Viewer — Hanya Melihat Data & Riwayat Audit Logs
                                    </option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                        Super Administrator — Akses Penuh ke Seluruh Sistem
                                    </option>
                                </select>
                                @if($user->id === auth()->id())
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <div class="form-text text-warning small mt-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Anda tidak dapat mengubah peran akun Anda sendiri saat sedang login.
                                </div>
                                @endif
                                @error('role')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Profil Ringkas Akun & Panduan Hak Akses -->
                <div class="col-12 col-lg-5">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <!-- User Summary Badge -->
                            <div class="p-3 bg-white rounded-3 border mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-teal-subtle text-teal fw-bold fs-4 d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 52px; height: 52px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                            <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $user->name }}</h6>
                                            @if($user->role === 'admin')
                                                <span class="badge badge-lost-mode fw-semibold">ADMIN</span>
                                            @elseif($user->role === 'operator')
                                                <span class="badge badge-compliant fw-semibold">OPERATOR</span>
                                            @else
                                                <span class="badge bg-light text-secondary border fw-semibold">AUDITOR</span>
                                            @endif
                                        </div>
                                        <span class="text-secondary small font-mono d-block">{{ '@' . $user->username }}</span>
                                        <div class="text-muted mt-1" style="font-size: 11px;">
                                            <i class="bi bi-calendar3 me-1"></i> Terdaftar sejak {{ $user->created_at->format('d M Y, H:i') }} WIB
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-teal fs-5"></i>
                                <span>Informasi Peran Pengguna</span>
                            </h6>

                            <div class="d-flex flex-column gap-2 mb-4">
                                <div class="bg-white p-3 rounded-3 border">
                                    <div class="small fw-semibold text-dark mb-1">
                                        <i class="bi bi-check2-circle text-teal me-1"></i> Wewenang Saat Ini:
                                    </div>
                                    <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.4;">
                                        @if($user->role === 'admin')
                                            Akun memiliki wewenang penuh untuk mengelola pengguna lain, konfigurasi global AMAPI, audit trail, serta remote actions.
                                        @elseif($user->role === 'operator')
                                            Akun berwenang mengelola armada perangkat, mengirim perintah wipe/lock/reboot, dan membuat QR code pendaftaran.
                                        @else
                                            Akun memiliki hak akses pantau dan peninjauan riwayat audit tanpa wewenang mengubah data atau perangkat.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Security Note -->
                        <div class="alert alert-light border rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                            <i class="bi bi-shield-lock-fill text-teal fs-5 flex-shrink-0"></i>
                            <div class="small text-secondary">
                                <strong>Audit Trail:</strong> Perubahan role atau pembaruan profil pengguna ini akan tercatat dalam log audit sistem untuk tujuan pengawasan.
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
                <span>Terakhir diperbarui: <strong>{{ $user->updated_at->format('d M Y, H:i') }} WIB</strong></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm px-3">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm px-4 d-flex align-items-center gap-2">
                    <i class="bi bi-save"></i>
                    <span>Perbarui Pengguna</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
