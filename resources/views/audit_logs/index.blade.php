@extends('layouts.app')

@section('title', 'Audit Logs')
@section('page_title', 'Riwayat Aktivitas & Audit Trail')

@section('content')
<!-- Filter Card -->
<div class="card card-mdm border-0 p-3 mb-4">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari operator, target device, IP address..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-4">
            <input type="text" name="action" class="form-control" placeholder="Filter Jenis Aksi (misal: COMMAND_LOCK)" value="{{ request('action') }}">
        </div>
        <div class="col-6 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
            @if(request()->hasAny(['search', 'action']))
            <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Logs Table Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Rekam Jejak Sistem ({{ $logs->total() }})</h5>
            <span class="text-secondary small">Seluruh mutasi data dan perintah remote tercatat secara permanen</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">Waktu (WIB)</th>
                    <th scope="col">Operator</th>
                    <th scope="col">Aktivitas / Aksi</th>
                    <th scope="col">Target</th>
                    <th scope="col">Rincian Perubahan (Payload)</th>
                    <th scope="col" class="pe-4">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="ps-4 font-mono text-secondary small">
                        {{ $log->created_at->format('d M Y H:i:s') }}
                    </td>
                    <td>
                        @if($log->user)
                            <div class="fw-bold text-dark">{{ $log->user->name ?? $log->user->username }}</div>
                            <span class="badge bg-light text-secondary border small">{{ ucfirst($log->user->role) }}</span>
                        @else
                            <span class="badge bg-light text-muted border">System / Cron</span>
                        @endif
                    </td>
                    <td>
                        @if(str_contains($log->action, 'COMMAND_') || str_contains($log->action, 'DISPATCH_'))
                            <span class="badge badge-warning-custom font-mono">{{ $log->action }}</span>
                        @elseif(str_contains($log->action, 'DELETE_') || str_contains($log->action, 'UNENROLL'))
                            <span class="badge badge-lost-mode font-mono">{{ $log->action }}</span>
                        @elseif(str_contains($log->action, 'LOGIN'))
                            <span class="badge badge-compliant font-mono">{{ $log->action }}</span>
                        @else
                            <span class="badge bg-light text-teal border border-teal-subtle font-mono">{{ $log->action }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="small fw-semibold text-dark">{{ $log->target_type ?? '-' }}</div>
                        <span class="font-mono text-muted small" title="{{ $log->target_id }}">
                            {{ $log->target_id ? (strlen($log->target_id) > 25 ? '...' . substr($log->target_id, -20) : $log->target_id) : '-' }}
                        </span>
                    </td>
                    <td style="max-width: 250px;">
                        @if(!empty($log->new_values))
                            <span class="font-mono text-secondary small text-truncate d-block" style="font-size: 11px;">
                                {{ json_encode($log->new_values) }}
                            </span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="pe-4 font-mono text-secondary small">
                        {{ $log->ip_address ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-secondary">
                        <i class="bi bi-journal-x text-muted fs-1 d-block mb-2"></i>
                        Belum ada catatan log aktivitas yang terekam.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="card-footer bg-white border-top py-3 px-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
