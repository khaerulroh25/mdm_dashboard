@extends('layouts.app')

@section('title', 'Edit Pengguna: ' . $user->username)
@section('page_title', 'Manajemen Pengguna Dashboard')

@section('content')
@php
    $selectedRole = old('role', $user->role);
    $activePerms = old('permissions', $effectivePermissions);
@endphp

<!-- Harmonized User Edit Card with Permission Matrix -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <!-- Header with Back Button -->
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Edit Pengguna & Permission: <span class="font-mono text-teal">{{ $user->username }}</span></h5>
            <span class="text-secondary small">Perbarui data profil, peran utama, password, serta pengaturan hak akses (permission) akun</span>
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
        <input type="hidden" name="permissions_submitted" value="1">
        
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left Column: Form Kredensial & Profil Pengguna -->
                <div class="col-12 col-lg-5">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <!-- User Summary Badge -->
                            <div class="p-3 bg-white rounded-3 border mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-teal-subtle text-teal fw-bold fs-4 d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 50px; height: 50px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                            <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $user->name }}</h6>
                                            @if($user->role === 'admin')
                                                <span class="badge badge-lost-mode fw-semibold">SUPERADMIN</span>
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
                                <i class="bi bi-person-gear text-teal fs-5"></i>
                                <span>Perbarui Data Profil & Kredensial</span>
                            </h6>

                            <div class="row g-3">
                                <!-- Username -->
                                <div class="col-12">
                                    <label for="username" class="form-label small fw-semibold text-secondary">
                                        Username <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="username" id="username" 
                                        class="form-control font-mono @error('username') is-invalid @enderror" 
                                        value="{{ old('username', $user->username) }}" required>
                                    @error('username')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Full Name -->
                                <div class="col-12">
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
                                <div class="col-12">
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
                                <div class="col-12">
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
                                        Peran Utama (Role Preset) <span class="text-danger">*</span>
                                    </label>
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <option value="operator" {{ $selectedRole == 'operator' ? 'selected' : '' }}>
                                            Operator Lapangan (Preset Operasional)
                                        </option>
                                        <option value="auditor" {{ $selectedRole == 'auditor' ? 'selected' : '' }}>
                                            Auditor / Viewer (Preset Read-Only & Audit)
                                        </option>
                                        <option value="admin" {{ $selectedRole == 'admin' ? 'selected' : '' }}>
                                            Super Administrator (Akses Penuh Seluruh Sistem)
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

                                <!-- Branch Assignment -->
                                <div class="col-12" id="branchSelectWrapper">
                                    <label for="branch_id" class="form-label small fw-semibold text-secondary">
                                        Penempatan Cabang (Wilayah Operasional)
                                    </label>
                                    <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                        <option value="" {{ old('branch_id', $user->branch_id) == '' ? 'selected' : '' }}>
                                            🏢 Pusat / Lintas Cabang (Akses Seluruh Cabang)
                                        </option>
                                        @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id) == $b->id ? 'selected' : '' }}>
                                            📍 {{ $b->name }} ({{ $b->code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text" style="font-size: 11px;">
                                        Jika cabang dipilih, akun ini <strong>hanya dapat melihat dan mengelola perangkat milik cabang tersebut</strong>.
                                    </div>
                                    @error('branch_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Security Note -->
                        <div class="alert alert-light border rounded-3 p-3 mt-4 mb-0 d-flex align-items-center gap-3">
                            <i class="bi bi-shield-lock-fill text-teal fs-5 flex-shrink-0"></i>
                            <div class="small text-secondary">
                                <strong>Audit Trail:</strong> Setiap perubahan role atau permission akun ini akan tercatat di dalam riwayat Audit Logs.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Pengaturan Permission (Granular Checklist) -->
                <div class="col-12 col-lg-7">
                    <div class="p-4 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-sliders text-teal fs-5"></i>
                                    <span>Pengaturan Hak Akses (Permission Matrix)</span>
                                </h6>
                                <div class="d-flex align-items-center gap-1" id="permissionQuickActions">
                                    <button type="button" class="btn btn-outline-teal btn-sm py-1 px-2" style="font-size: 11px;" id="btnResetPreset">
                                        <i class="bi bi-arrow-counterclockwise"></i> Default Role
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size: 11px;" id="btnCheckAll">
                                        Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size: 11px;" id="btnUncheckAll">
                                        Kosongkan
                                    </button>
                                </div>
                            </div>

                            <!-- Banner for Superadmin Full Access -->
                            <div id="superadminBanner" class="alert border-0 rounded-3 p-3 mb-3 d-none align-items-center gap-2" style="background-color: #F0FDFA; border-left: 4px solid var(--bs-primary) !important; color: #0F766E;">
                                <i class="bi bi-patch-check-fill fs-5 text-teal"></i>
                                <div class="small">
                                    <strong>Mode Super Administrator Aktif:</strong> Akun ini memiliki otoritas penuh (All Access) ke seluruh modul dan fitur sistem secara otomatis.
                                </div>
                            </div>

                            @foreach($permissionGroups as $groupName => $permissions)
                            <div class="mb-3">
                                <div class="text-uppercase fw-bold text-secondary mb-2 px-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                    {{ $groupName }}
                                </div>
                                <div class="row g-2">
                                    @foreach($permissions as $permKey => $permMeta)
                                    <div class="col-12 col-md-6">
                                        <div class="bg-white p-3 rounded-3 border h-100 d-flex flex-column justify-content-between permission-card">
                                            <div class="form-check form-switch mb-1">
                                                <input class="form-check-input permission-checkbox" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permKey }}" 
                                                       id="perm_{{ str_replace('.', '_', $permKey) }}"
                                                       {{ in_array($permKey, $activePerms) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold small text-dark" for="perm_{{ str_replace('.', '_', $permKey) }}">
                                                    {{ $permMeta['label'] }}
                                                </label>
                                            </div>
                                            <div class="text-secondary" style="font-size: 11px; line-height: 1.35;">
                                                {{ $permMeta['desc'] }}
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rolePresets = @json($rolePresets);
    const roleSelect = document.getElementById('role');
    const branchSelect = document.getElementById('branch_id');
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const superadminBanner = document.getElementById('superadminBanner');
    const quickActions = document.getElementById('permissionQuickActions');

    function applyRoleState(role, updateCheckboxes = false) {
        const isSuperAdmin = (role === 'admin');
        const presetList = rolePresets[role] || [];

        if (isSuperAdmin) {
            superadminBanner.classList.remove('d-none');
            superadminBanner.classList.add('d-flex');
            quickActions.classList.add('d-none');
            if (branchSelect) {
                branchSelect.value = '';
                branchSelect.disabled = true;
            }
            checkboxes.forEach(cb => {
                cb.checked = true;
                cb.disabled = true;
            });
        } else {
            superadminBanner.classList.add('d-none');
            superadminBanner.classList.remove('d-flex');
            quickActions.classList.remove('d-none');
            if (branchSelect) {
                branchSelect.disabled = false;
            }
            checkboxes.forEach(cb => {
                cb.disabled = false;
                if (updateCheckboxes) {
                    cb.checked = presetList.includes(cb.value);
                }
            });
        }
    }

    roleSelect.addEventListener('change', function () {
        applyRoleState(this.value, true);
    });

    document.getElementById('btnResetPreset').addEventListener('click', function () {
        applyRoleState(roleSelect.value, true);
    });

    document.getElementById('btnCheckAll').addEventListener('click', function () {
        checkboxes.forEach(cb => { if (!cb.disabled) cb.checked = true; });
    });

    document.getElementById('btnUncheckAll').addEventListener('click', function () {
        checkboxes.forEach(cb => { if (!cb.disabled) cb.checked = false; });
    });

    // Initial state on load (preserve existing custom permissions unless role is admin)
    applyRoleState(roleSelect.value, false);
});
</script>
@endpush
@endsection
