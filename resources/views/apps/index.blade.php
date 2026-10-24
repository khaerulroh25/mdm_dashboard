@extends('layouts.app')

@section('title', 'Katalog Aplikasi')
@section('page_title', 'Katalog Aplikasi Android')

@section('content')
<!-- Search & Action Toolbar -->
<div class="card card-mdm border-0 p-3 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form method="GET" action="{{ route('apps.index') }}" class="d-flex gap-2 flex-grow-1" style="max-width: 500px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama aplikasi atau package..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary px-3">Cari</button>
            @if(request('search'))
            <a href="{{ route('apps.index') }}" class="btn btn-outline-secondary" title="Reset">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>

        @if(auth()->user()->hasPermission('apps.create'))
        <div class="d-flex gap-2">
            <a href="{{ route('apps.search') }}" class="btn btn-teal d-flex align-items-center gap-1">
                <i class="bi bi-google-play"></i>
                <span>Cari di Google Play Store</span>
            </a>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#manualAddModal">
                <i class="bi bi-plus-lg"></i> Manual
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Apps Table Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Daftar Aplikasi Tersimpan ({{ $apps->total() }})</h5>
            <span class="text-secondary small">Katalog aplikasi yang dapat didistribusikan ke perangkat managed</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4" style="width: 60px;">Icon</th>
                    <th scope="col">Nama Aplikasi</th>
                    <th scope="col">Package Name (Identifier)</th>
                    <th scope="col">Terpasang di Policy</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($apps as $app)
                <tr>
                    <td class="ps-4">
                        @if($app->icon_url)
                            <img src="{{ $app->icon_url }}" alt="{{ $app->app_name }}" class="rounded-2 shadow-sm" style="width: 38px; height: 38px; object-fit: cover;">
                        @else
                            <div class="rounded-2 bg-light d-flex align-items-center justify-content-center text-secondary border" style="width: 38px; height: 38px;">
                                <i class="bi bi-app fs-5"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $app->app_name }}</div>
                        <span class="text-muted small">{{ $app->developer ?? 'Google Play Store' }}</span>
                    </td>
                    <td>
                        <span class="package-name text-dark fw-medium">{{ $app->package_name }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-teal border border-teal-subtle">
                            <i class="bi bi-shield me-1"></i> {{ $app->policies_count }} Policy
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        @if(auth()->user()->hasPermission('apps.delete'))
                        <form action="{{ route('apps.destroy', $app->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus aplikasi {{ $app->app_name }} dari katalog?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus dari Katalog">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @else
                        <span class="badge bg-light text-secondary border small">Tersimpan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-secondary">
                        <i class="bi bi-app-indicator text-muted fs-1 d-block mb-2"></i>
                        Belum ada aplikasi yang sesuai dengan pencarian Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($apps->hasPages())
    <div class="card-footer bg-white border-top py-3 px-4">
        {{ $apps->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Modal: Add Manual App -->
<div class="modal fade" id="manualAddModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('apps.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Tambah Aplikasi Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="app_name" class="form-label small fw-semibold">Nama Aplikasi</label>
                        <input type="text" name="app_name" id="app_name" class="form-control" placeholder="Contoh: Google Chrome" required>
                    </div>
                    <div class="mb-3">
                        <label for="package_name" class="form-label small fw-semibold">Package Name</label>
                        <input type="text" name="package_name" id="package_name" class="form-control font-mono" placeholder="Contoh: com.android.chrome" required>
                    </div>
                    <div class="mb-3">
                        <label for="policy_id" class="form-label small fw-semibold">Pasang Langsung ke Policy (Opsional)</label>
                        <select name="policy_id" id="policy_id" class="form-select">
                            <option value="">Hanya simpan di katalog</option>
                            @foreach($policies as $pol)
                            <option value="{{ $pol->policy_id }}">{{ $pol->name }} ({{ $pol->policy_id }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal btn-sm">Simpan Aplikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
