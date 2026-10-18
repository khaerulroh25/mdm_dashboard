@extends('layouts.app')

@section('title', 'Manage Users')
@section('page_title', 'Manajemen Pengguna Dashboard')

@section('content')
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Daftar Pengguna ({{ $users->total() }})</h5>
            <span class="text-secondary small">Kelola akun administrator, operator lapangan, dan auditor sistem</span>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill"></i>
                <span>Tambah Pengguna</span>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">Username</th>
                    <th scope="col">Nama Lengkap</th>
                    <th scope="col">Email</th>
                    <th scope="col">Hak Akses (Role)</th>
                    <th scope="col">Waktu Dibuat</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                <tr>
                    <td class="ps-4 text-secondary small">{{ $users->firstItem() + $index }}</td>
                    <td>
                        <div class="fw-bold text-dark font-mono">{{ $u->username }}</div>
                    </td>
                    <td>
                        <div class="text-dark">{{ $u->name }}</div>
                    </td>
                    <td>
                        <span class="text-secondary small">{{ $u->email ?? '-' }}</span>
                    </td>
                    <td>
                        @if($u->role === 'admin')
                            <span class="badge badge-lost-mode fw-semibold">
                                <i class="bi bi-shield-shaded me-1"></i> ADMIN
                            </span>
                        @elseif($u->role === 'operator')
                            <span class="badge badge-compliant fw-semibold">
                                <i class="bi bi-person-gear me-1"></i> OPERATOR
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border fw-semibold">
                                <i class="bi bi-eye me-1"></i> AUDITOR
                            </span>
                        @endif
                    </td>
                    <td class="text-secondary small font-mono">
                        {{ $u->created_at->format('d M Y') }}
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-outline-teal" title="Edit Pengguna">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengguna {{ $u->username }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        Belum ada pengguna terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white border-top py-3 px-4">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
