@extends('layouts.app')

@section('title', 'Master Cabang (Branches)')
@section('page_title', 'Manajemen Master Cabang')

@section('content')
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Daftar Master Cabang ({{ $branches->total() }})</h5>
            <span class="text-secondary small">Kelola data cabang operasional untuk isolasi pengelolaan perangkat dan penempatan admin cabang</span>
        </div>
        <div>
            <a href="{{ route('branches.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-building-add"></i>
                <span>Tambah Cabang Baru</span>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">Kode Cabang</th>
                    <th scope="col">Nama Cabang</th>
                    <th scope="col">Enterprise & Policy</th>
                    <th scope="col">Kontak Darurat</th>
                    <th scope="col">Perangkat Aktif</th>
                    <th scope="col">Akun Cabang</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $index => $branch)
                <tr>
                    <td class="ps-4 text-secondary small">{{ $branches->firstItem() + $index }}</td>
                    <td>
                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono fw-bold px-2 py-1">
                            {{ $branch->code }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $branch->name }}</div>
                        @if($branch->address)
                            <div class="text-secondary small text-truncate" style="max-width: 260px;">{{ $branch->address }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="small fw-semibold text-dark">
                            <i class="bi bi-building text-teal me-1"></i>{{ $branch->enterprise->name ?? 'Default (Active)' }}
                        </div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary font-mono" style="font-size: 11px;">
                            {{ $branch->default_policy_id ?: 'Mode_kiosk' }}
                        </span>
                    </td>
                    <td>
                        @if($branch->contact_phone)
                            <span class="font-mono small text-dark">
                                <i class="bi bi-telephone text-teal me-1"></i>{{ $branch->contact_phone }}
                            </span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('devices.index', ['branch_id' => $branch->id]) }}" class="text-decoration-none">
                            <span class="badge bg-light text-dark border font-mono">
                                <i class="bi bi-phone text-teal me-1"></i> {{ $branch->active_devices_count }} Aktif
                            </span>
                        </a>
                        @if($branch->devices_count > $branch->active_devices_count)
                            <span class="text-muted small ms-1">({{ $branch->devices_count }} total)</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border font-mono">
                            <i class="bi bi-people me-1"></i> {{ $branch->users_count }} Akun
                        </span>
                    </td>
                    <td>
                        @if($branch->is_active)
                            <span class="badge badge-compliant">
                                <i class="bi bi-check-circle-fill me-1"></i> AKTIF
                            </span>
                        @else
                            <span class="badge bg-secondary text-white">
                                <i class="bi bi-slash-circle me-1"></i> NONAKTIF
                            </span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('devices.index', ['branch_id' => $branch->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Perangkat Cabang Ini">
                                <i class="bi bi-phone"></i>
                            </a>
                            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-outline-teal" title="Edit Cabang">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </a>
                            @if($branch->devices_count === 0 && $branch->users_count === 0)
                            <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus cabang {{ $branch->name }} ({{ $branch->code }})?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Cabang">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-secondary">
                        <i class="bi bi-building text-muted fs-1 d-block mb-2"></i>
                        Belum ada cabang yang terdaftar. Klik <strong>Tambah Cabang Baru</strong> untuk memulai.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($branches->hasPages())
    <div class="card-footer bg-white border-top py-3 px-4">
        {{ $branches->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
