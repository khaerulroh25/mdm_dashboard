@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page_title', 'Tambah Pengguna Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-xl-6">
        <div class="card card-mdm border-0">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">Formulir Tambah Pengguna</h5>
                <span class="text-secondary small">Buat akun untuk admin, operator, atau auditor sistem</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                        <input type="text" name="username" id="username" class="form-control font-mono @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Contoh: operator_jakarta" required autofocus>
                        @error('username')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nama petugas..." required>
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email (Opsional)</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@perusahaan.com">
                        @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold text-secondary">Password Awal</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                        @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label small fw-semibold text-secondary">Peran & Hak Akses (Role)</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator Lapangan (Kelola Perangkat, Remote Command, Enrollment)</option>
                            <option value="auditor" {{ old('role') == 'auditor' ? 'selected' : '' }}>Auditor / Viewer (Hanya Melihat Data & Audit Logs)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Super Administrator (Akses Penuh Seluruh Sistem)</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-save me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
