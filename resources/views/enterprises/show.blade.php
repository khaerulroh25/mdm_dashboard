@extends('layouts.app')

@section('title', 'Detail Enterprise: ' . $enterprise->name)
@section('page_title', 'Detail Enterprise Google AMAPI')

@section('content')
<!-- Breadcrumb & Navigation -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-teal text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('enterprises.index') }}" class="text-teal text-decoration-none">Kelola Enterprise</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $enterprise->name }}</li>
        </ol>
    </nav>
    <div>
        <a href="{{ route('enterprises.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Enterprise
        </a>
    </div>
</div>

<!-- Header & Quick Information Grid -->
<div class="row g-3 mb-4">
    <!-- Card Info Enterprise -->
    <div class="col-12 col-lg-7">
        <div class="card card-mdm border-0 h-100 p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-teal bg-opacity-10 text-teal p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-building-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $enterprise->name }}</h5>
                        <span class="font-mono text-secondary small">{{ $enterprise->enterprise_id }}</span>
                    </div>
                </div>
                <div>
                    @if($enterprise->is_active)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle font-mono px-3 py-1 fs-6">
                            <i class="bi bi-check-circle-fill me-1"></i>AKTIF (DIGUNAKAN)
                        </span>
                    @else
                        <span class="badge bg-light text-secondary border font-mono px-3 py-1 fs-6">
                            STANDBY
                        </span>
                    @endif
                </div>
            </div>

            <div class="row g-3 small border-top pt-3">
                <div class="col-sm-6">
                    <span class="text-secondary d-block">Email Kontak Petugas:</span>
                    <strong class="text-dark">{{ $enterprise->admin_email ?: '-' }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-secondary d-block">Nomor Telepon:</span>
                    <strong class="font-mono text-dark">{{ $enterprise->admin_phone ?: '-' }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-secondary d-block">Tipe Akun Google Play:</span>
                    <span class="text-dark">{{ $enterprise->metadata['enterpriseType'] ?? 'MANAGED_GOOGLE_PLAY_ACCOUNTS_ENTERPRISE' }}</span>
                </div>
                <div class="col-sm-6">
                    <span class="text-secondary d-block">Tanggal Didaftarkan:</span>
                    <span class="text-dark">{{ $enterprise->created_at ? $enterprise->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Kuota Armada Google AMAPI -->
    <div class="col-12 col-lg-5">
        <div class="card card-mdm border-0 h-100 p-4 bg-light">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">
                    <i class="bi bi-speedometer2 text-teal me-1"></i>Kapasitas Kuota Google AMAPI
                </span>
                <span class="badge bg-white text-secondary border font-mono">BATAS: 500 UNIT</span>
            </div>

            <div class="d-flex align-items-baseline gap-2 mb-2">
                <h2 class="fw-bold mb-0 text-dark">{{ number_format($deviceCount) }}</h2>
                <span class="text-secondary">/ 500 perangkat terdaftar</span>
            </div>

            @php
                $percent = min(100, round(($deviceCount / 500) * 100, 1));
                $progressColor = $percent > 80 ? 'bg-danger' : ($percent > 50 ? 'bg-warning' : 'bg-teal');
            @endphp
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between text-secondary small" style="font-size: 11px;">
                <span>Terpakai: <strong>{{ $percent }}%</strong></span>
                <span>Tersedia: <strong>{{ max(0, 500 - $deviceCount) }} slot</strong></span>
            </div>

            <div class="mt-auto pt-3 border-top small text-secondary">
                <i class="bi bi-shield-check text-teal me-1"></i>
                Setiap Enterprise memiliki kuota pendaftaran mandiri 500 perangkat dari Google.
            </div>
        </div>
    </div>
</div>

<!-- Card 1: Daftar Policies Terdaftar di Enterprise Ini -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-shield-lock-fill text-teal me-2"></i>Daftar Kebijakan (Policies) di Google AMAPI
            </h5>
            <span class="text-secondary small">Data kebijakan real-time yang tersimpan di Google Cloud untuk Enterprise ini ({{ count($policies) }} Policy)</span>
        </div>
        <div>
            <form action="{{ route('enterprises.clone-policies', $enterprise->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menyinkronkan/mengklon ulang ketiga policy master (Mode_kiosk, Gagal_bayar, Lunas) ke Enterprise ini?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Sinkronkan / Klon Ulang Policy Master</span>
                </button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4" style="width: 50px;">No</th>
                    <th scope="col">Nama Policy</th>
                    <th scope="col">Versi AMAPI</th>
                    <th scope="col">Mode Kiosk & Aplikasi</th>
                    <th scope="col">Proteksi FRP Email Kantor</th>
                    <th scope="col">Reset Pabrik</th>
                    <th scope="col">Mode Lokasi GPS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($policies as $index => $pol)
                <tr>
                    <td class="ps-4 text-secondary small">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold text-dark font-mono fs-6">
                            @if($pol['short_id'] === 'Mode_kiosk')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle py-1 px-2">
                                    <i class="bi bi-shield-check me-1"></i>Mode_kiosk
                                </span>
                            @elseif($pol['short_id'] === 'Gagal_bayar')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle py-1 px-2">
                                    <i class="bi bi-lock-fill me-1"></i>Gagal_bayar
                                </span>
                            @elseif($pol['short_id'] === 'Lunas')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle py-1 px-2">
                                    <i class="bi bi-patch-check-fill me-1"></i>Lunas
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-dark border py-1 px-2">
                                    {{ $pol['short_id'] }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border font-mono">
                            v{{ $pol['version'] ?? '1' }}
                        </span>
                    </td>
                    <td>
                        @if($pol['kiosk_app'])
                            <div class="small fw-semibold text-dark">
                                <i class="bi bi-fullscreen text-teal me-1"></i>Kiosk Tunggal
                            </div>
                            <span class="font-mono text-secondary" style="font-size: 11px;">{{ $pol['kiosk_app'] }}</span>
                        @elseif($pol['applications_count'] > 0)
                            <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle small">
                                {{ $pol['applications_count'] }} Aplikasi Terpasang
                            </span>
                        @else
                            <span class="text-muted small fst-italic">Bebas Aplikasi (0 App)</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($pol['frp_admin_emails']))
                            <div class="small text-dark font-mono">
                                <i class="bi bi-shield-lock-fill text-success me-1"></i>{{ implode(', ', $pol['frp_admin_emails']) }}
                            </div>
                        @else
                            <span class="badge bg-light text-secondary border small">Tidak Ada (Bebas Reset)</span>
                        @endif
                    </td>
                    <td>
                        @if($pol['factory_reset_disabled'])
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle small">
                                <i class="bi bi-slash-circle me-1"></i>Diblokir
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle small">
                                <i class="bi bi-check2 me-1"></i>Diizinkan
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($pol['location_mode'] === 'LOCATION_ENFORCED')
                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle small">
                                <i class="bi bi-geo-alt-fill me-1"></i>Wajib Aktif (Enforced)
                            </span>
                        @else
                            <span class="text-secondary small font-mono">{{ $pol['location_mode'] }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-shield-x text-muted fs-1 d-block mb-2"></i>
                        Belum ada policy yang terdaftar di Enterprise ini pada Google Cloud AMAPI.
                        <div class="mt-2">
                            <form action="{{ route('enterprises.clone-policies', $enterprise->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-teal">
                                    <i class="bi bi-arrow-repeat me-1"></i>Kloning 3 Policy Master Sekarang
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Card 2: Cabang Operasional yang Menggunakan Enterprise Ini -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-building text-teal me-2"></i>Cabang Operasional yang Ditautkan ({{ $enterprise->branches->count() }})
            </h5>
            <span class="text-secondary small">Cabang yang otomatis menggunakan akun Enterprise ini saat membuat QR pendaftaran perangkat</span>
        </div>
        <div>
            <a href="{{ route('branches.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-gear-fill"></i>
                <span>Kelola di Master Cabang</span>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4" style="width: 50px;">No</th>
                    <th scope="col">Kode Cabang</th>
                    <th scope="col">Nama Cabang</th>
                    <th scope="col">Kontak Darurat</th>
                    <th scope="col">Default Policy Pendaftaran</th>
                    <th scope="col">Status Cabang</th>
                    <th scope="col" class="text-end pe-4">Armada Aktif</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enterprise->branches as $bIndex => $br)
                <tr>
                    <td class="ps-4 text-secondary small">{{ $bIndex + 1 }}</td>
                    <td>
                        <span class="badge bg-light text-dark border font-mono fs-6">{{ $br->code }}</span>
                    </td>
                    <td>
                        <strong class="text-dark">{{ $br->name }}</strong>
                    </td>
                    <td class="font-mono text-secondary small">
                        {{ $br->contact_phone ?: '-' }}
                    </td>
                    <td>
                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono">
                            {{ $br->default_policy_id ?: 'Mode_kiosk' }}
                        </span>
                    </td>
                    <td>
                        @if($br->is_active)
                            <span class="badge bg-success small">Aktif</span>
                        @else
                            <span class="badge bg-secondary small">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <span class="fw-bold text-dark font-mono">{{ $br->activeDevices->count() }}</span>
                        <span class="text-secondary small">HP</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-secondary">
                        <i class="bi bi-info-circle text-muted fs-3 d-block mb-1"></i>
                        Belum ada cabang operasional yang ditautkan ke akun Enterprise ini.
                        <div class="small mt-1">
                            Anda dapat menautkan cabang ke Enterprise ini melalui menu <a href="{{ route('branches.index') }}" class="text-teal text-decoration-none fw-semibold">Master Cabang</a>.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
