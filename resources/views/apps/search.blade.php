@extends('layouts.app')

@section('title', 'Cari di Google Play Store')
@section('page_title', 'Pencarian Google Play Store')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('apps.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Katalog">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">Eksplorasi Google Play Store</h4>
            <span class="text-secondary small">Cari aplikasi publik untuk didistribusikan ke perangkat Anda</span>
        </div>
    </div>
</div>

<!-- Search Input Card -->
<div class="card card-mdm border-0 p-4 mb-4">
    <form method="GET" action="{{ route('apps.search') }}" class="row g-2">
        <div class="col-12 col-md-10">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white border-end-0 text-secondary">
                    <i class="bi bi-google-play fs-4 text-teal"></i>
                </span>
                <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Ketik kata kunci aplikasi (misal: WhatsApp, TikTok, Zoom, Gmail)..." value="{{ $keyword }}" autofocus required>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                <i class="bi bi-search me-1"></i> Cari
            </button>
        </div>
    </form>
</div>

<!-- Search Results Grid -->
@if(!empty($keyword))
<div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="fw-bold text-dark mb-0">
        Hasil Pencarian untuk: <span class="text-teal font-mono">"{{ $keyword }}"</span>
    </h6>
    <span class="text-secondary small">{{ count($results) }} aplikasi ditemukan</span>
</div>

<div class="row g-3 mb-4">
    @forelse($results as $index => $app)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-mdm border-0 h-100 p-3 d-flex flex-column justify-content-between">
            <div class="d-flex gap-3 mb-3">
                @if(!empty($app['icon']))
                    <img src="{{ $app['icon'] }}" alt="{{ $app['app_name'] }}" class="rounded-3 shadow-sm flex-shrink-0" style="width: 54px; height: 54px; object-fit: cover;">
                @else
                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-teal border flex-shrink-0" style="width: 54px; height: 54px;">
                        <i class="bi bi-google-play fs-3"></i>
                    </div>
                @endif
                <div class="overflow-hidden">
                    <h6 class="fw-bold text-dark text-truncate mb-1" title="{{ $app['app_name'] }}">{{ $app['app_name'] }}</h6>
                    <span class="text-secondary small d-block text-truncate mb-1">{{ $app['developer'] ?? 'Google Play' }}</span>
                    <span class="package-name text-muted small d-block text-truncate" style="font-size: 11px;">{{ $app['package_name'] }}</span>
                </div>
            </div>

            <form action="{{ route('apps.store') }}" method="POST" class="border-top pt-3">
                @csrf
                <input type="hidden" name="package_name" value="{{ $app['package_name'] }}">
                <input type="hidden" name="app_name" value="{{ $app['app_name'] }}">
                <input type="hidden" name="icon_url" value="{{ $app['icon'] ?? '' }}">
                <input type="hidden" name="developer" value="{{ $app['developer'] ?? '' }}">

                <div class="row g-1 align-items-center">
                    <div class="col-7">
                        <select name="policy_id" class="form-select form-select-sm" style="font-size: 12px;">
                            <option value="">Hanya Katalog</option>
                            @foreach($policies as $pol)
                            <option value="{{ $pol->policy_id }}">+ {{ $pol->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-5">
                        <button type="submit" class="btn btn-sm btn-teal w-100 d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-plus-lg"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card card-mdm border-0 p-5 text-center text-secondary">
            <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
            Tidak ada aplikasi yang ditemukan untuk kata kunci "{{ $keyword }}".
        </div>
    </div>
    @endforelse
</div>
@endif
@endsection
