@extends('layouts.app')

@section('title', 'Edit Pengguna: ' . $user->username)
@section('page_title', 'Perbarui Pengguna')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-xl-6">
        <div class="card card-mdm border-0">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">Edit Pengguna: {{ $user->username }}</h5>
                <span class="text-secondary small">Perbarui data profil, peran, atau ubah password</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                        <input type="text" name="username" id="username" class="form-control font-mono @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                        @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold text-secondary">Password Baru (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Biarkan kosong jika tetap menggunakan password lama">
                        @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label small fw-semibold text-secondary">Peran & Hak Akses (Role)</label>
                        <select name="role" id="role" class="form-select" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <option value="operator" {{ old('role', $user->role) == 'operator' ? 'selected' : '' }}>Operator Lapangan</option>
                            <option value="auditor" {{ old('role', $user->role) == 'auditor' ? 'selected' : '' }}>Auditor / Viewer</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Super Administrator</option>
                        </select>
                        @if($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <div class="form-text small text-muted">Anda tidak dapat mengubah peran akun Anda sendiri saat sedang login.</div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-save me-1"></i> Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
