@extends('layouts.app')

@section('title', 'Dashboard Overview')
@section('page_title', 'Dashboard Overview')

@section('content')
<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Metric 1: Total Perangkat -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box bg-teal-subtle">
                    <i class="bi bi-phone-fill fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Total Perangkat</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalDevices) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 2: Perangkat Aktif -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box" style="background-color: #ECFDF5; color: #10B981;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Aktif / Compliant</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($activeDevices) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 3: Mode Hilang / Lock -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box" style="background-color: #FFE4E6; color: #E11D48;">
                    <i class="bi bi-lock-fill fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Mode Hilang (Lost)</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($lostModeDevices) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 4: Total Aplikasi -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-mdm border-0 h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box" style="background-color: #F0F9FF; color: #0284C7;">
                    <i class="bi bi-google-play fs-4"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Katalog Aplikasi</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalApps) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enrollment Chart & Data Reconciliation Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-steps text-teal"></i>
                <span>Grafik Enrollment & Cek Kesesuaian Data</span>
                @if($enrollmentDate === now()->toDateString())
                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle small" style="font-size: 11px;">Hari Ini</span>
                @else
                    <span class="badge bg-light text-secondary border font-mono small" style="font-size: 11px;">{{ \Carbon\Carbon::parse($enrollmentDate)->translatedFormat('d M Y') }}</span>
                @endif
            </h5>
            <span class="text-secondary small">Memantau jumlah perangkat yang di-enroll beserta pemeriksaan kelengkapan data pemiliknya</span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Mode Switcher: Per Jam (Hari Ini) vs Tren 7 Hari -->
            <div class="btn-group btn-group-sm" role="group" aria-label="Mode Grafik">
                <button type="button" class="btn btn-outline-teal active" id="btnChartHourly">
                    <i class="bi bi-clock me-1"></i>Per Jam
                </button>
                <button type="button" class="btn btn-outline-teal" id="btnChartDaily">
                    <i class="bi bi-calendar-week me-1"></i>7 Hari Terakhir
                </button>
            </div>

            <!-- Date Selector -->
            <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 m-0">
                @if(!empty($selectedBranchId))
                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                @endif
                <input type="date" name="enrollment_date" class="form-control form-control-sm font-mono" value="{{ $enrollmentDate }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()" title="Pilih Tanggal Pengecekan Enrollment">
                @if($enrollmentDate !== now()->toDateString())
                    <a href="{{ route('dashboard', !empty($selectedBranchId) ? ['branch_id' => $selectedBranchId] : []) }}" class="btn btn-sm btn-outline-secondary" title="Kembali ke Hari Ini">
                        Hari Ini
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-4 align-items-stretch">
            <!-- Left Column: Interactive Chart -->
            <div class="col-12 col-lg-8">
                <div class="p-3 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 px-1">
                        <span class="small fw-semibold text-secondary" id="chartSubtitle">
                            Distribusi Jam Enrollment ({{ \Carbon\Carbon::parse($enrollmentDate)->translatedFormat('d F Y') }})
                        </span>
                        <div class="d-flex align-items-center gap-3 small">
                            <span class="d-inline-flex align-items-center gap-1 text-dark">
                                <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #0D9488;"></span>
                                Data Sesuai (Ada Pemilik)
                            </span>
                            <span class="d-inline-flex align-items-center gap-1 text-dark">
                                <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #F59E0B;"></span>
                                Belum Ditautkan
                            </span>
                        </div>
                    </div>

                    <div style="position: relative; height: 240px; width: 100%;">
                        <canvas id="enrollmentTodayChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right Column: Data Consistency & Reconciliation Summary -->
            <div class="col-12 col-lg-4">
                <div class="p-3 bg-light bg-opacity-50 rounded-3 border h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-clipboard2-check text-teal fs-5"></i>
                            <span>Ringkasan Kesesuaian Data</span>
                        </h6>

                        <!-- Today's Enrollment Reconciliation Box -->
                        <div class="bg-white p-3 rounded-3 border mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-secondary small fw-semibold">
                                    Enrollment {{ $enrollmentDate === now()->toDateString() ? 'Hari Ini' : \Carbon\Carbon::parse($enrollmentDate)->translatedFormat('d M Y') }}
                                </span>
                                <span class="badge bg-teal text-white font-mono">{{ $todayTotalCount }} Perangkat</span>
                            </div>

                            <div class="row g-2 text-center mt-1">
                                <div class="col-6">
                                    <div class="p-2 rounded-2 border" style="background-color: #ECFDF5;">
                                        <div class="fw-bold fs-5" style="color: #059669;">{{ $todayMatchedCount }}</div>
                                        <span class="text-secondary d-block" style="font-size: 11px;">Sudah Ada Pemilik</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-2 border" style="background-color: {{ $todayUnassignedCount > 0 ? '#FFFBEB' : '#F8FAFC' }};">
                                        <div class="fw-bold fs-5" style="color: {{ $todayUnassignedCount > 0 ? '#D97706' : '#64748B' }};">{{ $todayUnassignedCount }}</div>
                                        <span class="text-secondary d-block" style="font-size: 11px;">Belum Ditautkan</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Active Fleet Consistency Box -->
                        @php
                            $overallPercentage = $totalDevices > 0 ? round(($overallMatchedDevices / $totalDevices) * 100) : 100;
                        @endphp
                        <div class="bg-white p-3 rounded-3 border">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-secondary small fw-semibold">Kesesuaian Total Perangkat Aktif</span>
                                <span class="fw-bold small {{ $overallUnassignedDevices === 0 ? 'text-success' : 'text-warning' }}">
                                    {{ $overallPercentage }}% Sesuai
                                </span>
                            </div>

                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-teal" role="progressbar" style="width: {{ $overallPercentage }}%" aria-valuenow="{{ $overallPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between text-secondary" style="font-size: 11.5px;">
                                <span><strong class="text-dark">{{ $overallMatchedDevices }}</strong> Lengkap</span>
                                <span>•</span>
                                <span><strong class="text-dark">{{ $overallUnassignedDevices }}</strong> Belum Ada Pemilik</span>
                                <span>•</span>
                                <span>Total <strong class="text-dark">{{ $totalDevices }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Reconciliation Status Alert -->
                    <div class="mt-3">
                        @if($overallUnassignedDevices === 0 && $totalDevices > 0)
                            <div class="alert border-0 rounded-3 p-2 px-3 mb-0 d-flex align-items-center gap-2" style="background-color: #ECFDF5; color: #065F46; font-size: 12px;">
                                <i class="bi bi-check-circle-fill fs-6 text-success"></i>
                                <span><strong>Data Sesuai 100%:</strong> Seluruh perangkat aktif telah memiliki data pemilik.</span>
                            </div>
                        @elseif($overallUnassignedDevices > 0)
                            <div class="alert border-0 rounded-3 p-2 px-3 mb-0 d-flex align-items-center justify-content-between gap-2" style="background-color: #FFFBEB; color: #92400E; font-size: 12px;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-triangle-fill fs-6 text-warning"></i>
                                    <span>Ada <strong>{{ $overallUnassignedDevices }} perangkat</strong> belum ditautkan ke pemilik.</span>
                                </div>
                                <a href="{{ route('devices.index') }}" class="btn btn-sm btn-warning py-0 px-2 fw-semibold" style="font-size: 11px;">Cek</a>
                            </div>
                        @else
                            <div class="alert alert-light border rounded-3 p-2 px-3 mb-0 small text-secondary">
                                Belum ada perangkat aktif pada filter ini.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Fleet Table -->
<div class="card card-mdm border-0 mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2 rounded-top-3">
        <div>
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <span>Daftar Perangkat Aktif Terbaru</span>
                @if(!empty($currentBranch))
                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle small fw-semibold" style="font-size: 11px;">
                        <i class="bi bi-geo-alt-fill me-1"></i>{{ $currentBranch->name }}
                    </span>
                @endif
            </h5>
            <span class="text-secondary small">Menampilkan perangkat aktif yang baru didaftarkan atau diperbarui</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!auth()->user()->isBranchScoped() && isset($branches) && $branches->count() > 0)
            <form method="GET" action="{{ route('dashboard') }}" class="m-0">
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Cabang (Pusat)</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ ($selectedBranchId ?? '') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                    @endforeach
                </select>
            </form>
            @endif
            <a href="{{ route('devices.index', !empty($selectedBranchId) ? ['branch_id' => $selectedBranchId] : []) }}" class="btn btn-sm btn-outline-secondary">
                <span>Lihat Semua</span>
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">ID Anggota</th>
                    <th scope="col">Pemilik Perangkat</th>
                    <th scope="col">Tipe Perangkat</th>
                    <th scope="col">Policy Aktif</th>
                    <th scope="col">Status AMAPI</th>
                    <th scope="col">Device ID (Resource)</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDevices as $index => $device)
                <tr>
                    <td class="ps-4 text-secondary small">{{ $index + 1 }}</td>
                    <td>
                        @if($device->owner && $device->owner->id_anggota)
                            <span class="font-mono fw-semibold text-dark">{{ $device->owner->id_anggota }}</span>
                        @else
                            <span class="text-muted small">Belum ada</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $device->owner->nama ?? 'Belum Ditautkan' }}</div>
                        <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                            @if($device->branch)
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle" style="font-size: 10.5px;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ $device->branch->name }}
                                </span>
                            @endif
                            @if($device->owner && $device->owner->telepon)
                                @if($device->branch)
                                    <span class="text-muted small">•</span>
                                @endif
                                <span class="text-muted small font-mono">{{ $device->owner->telepon }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $device->human_model_name }}</div>
                        <span class="text-muted small font-mono">{{ $device->hardware_model ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge-policy">
                            <i class="bi bi-shield-check me-1 text-teal"></i>
                            {{ $device->policy ? $device->policy->name : ($device->policy_id ?? 'default') }}
                        </span>
                    </td>
                    <td>
                        @if($device->state === 'ACTIVE')
                            <span class="badge badge-compliant d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i> ACTIVE
                            </span>
                        @elseif($device->state === 'PROVISIONING')
                            <span class="badge badge-pending-custom d-inline-flex align-items-center gap-1">
                                <i class="bi bi-clock-history"></i> PROVISIONING
                            </span>
                        @else
                            <span class="badge badge-warning-custom d-inline-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ $device->state }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="amapi-path text-muted" title="{{ $device->device_id }}">
                            .../{{ substr($device->device_id, -16) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false">
                                <i class="bi bi-gear-fill text-secondary"></i>
                                <span>Kelola</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border py-1" style="min-width: 210px; z-index: 1060;">
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-dark" href="{{ route('devices.show', $device->id) }}">
                                        <i class="bi bi-info-circle-fill text-teal"></i>
                                        <span>Detail & Kontrol Penuh</span>
                                    </a>
                                </li>
                                @if(auth()->user()->hasPermission('devices.command'))
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('devices.command', $device->id) }}" method="POST" class="m-0" onsubmit="return confirm('Kunci layar perangkat {{ $device->owner->nama ?? $device->human_model_name }} sekarang?')">
                                        @csrf
                                        <input type="hidden" name="command_type" value="LOCK">
                                        <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-dark">
                                            <i class="bi bi-lock-fill text-secondary"></i>
                                            <span>Kunci Layar (Lock)</span>
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('devices.command', $device->id) }}" method="POST" class="m-0" onsubmit="return confirm('Reboot perangkat {{ $device->owner->nama ?? $device->human_model_name }} secara remote?')">
                                        @csrf
                                        <input type="hidden" name="command_type" value="REBOOT">
                                        <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-dark">
                                            <i class="bi bi-arrow-clockwise text-primary"></i>
                                            <span>Reboot Perangkat</span>
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-medium" data-bs-toggle="modal" data-bs-target="#lostModeModal{{ $device->id }}">
                                        <i class="bi bi-shield-exclamation text-danger"></i>
                                        <span>Aktifkan Lost Mode</span>
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-secondary">
                        <i class="bi bi-phone text-muted fs-1 d-block mb-2"></i>
                        Belum ada data perangkat yang tersimpan. Klik tombol <strong>Sync AMAPI</strong> di atas untuk sinkronisasi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()->hasPermission('devices.command'))
@foreach($recentDevices as $device)
<!-- Lost Mode Modal for Recent Device -->
<div class="modal fade" id="lostModeModal{{ $device->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('devices.command', $device->id) }}" method="POST">
                @csrf
                <input type="hidden" name="command_type" value="START_LOST_MODE">
                <div class="modal-header border-bottom bg-warning bg-opacity-10">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-shield-exclamation text-warning me-2"></i>Aktifkan Mode Hilang: {{ $device->owner->nama ?? $device->human_model_name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pesan Pada Layar Terkunci</label>
                        <textarea name="lost_message" class="form-control" rows="3" required>⚠️ Tagihan Anda Tertunggak. Silakan lakukan pembayaran segera.</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nomor Telepon Darurat Admin</label>
                        <input type="text" name="lost_phone" class="form-control font-mono" value="{{ $device->branch?->contact_phone ?: '0858-1406-4274' }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-semibold">Aktifkan Lost Mode</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('enrollmentTodayChart');
    if (!ctx) return;

    const hourlyData = {
        labels: @json($hourlyLabels),
        matched: @json($hourlyMatched),
        unassigned: @json($hourlyUnassigned),
        subtitle: 'Distribusi Jam Enrollment ({{ \Carbon\Carbon::parse($enrollmentDate)->translatedFormat("d F Y") }})'
    };

    const dailyData = {
        labels: @json($dailyLabels),
        matched: @json($dailyMatched),
        unassigned: @json($dailyUnassigned),
        subtitle: 'Tren Enrollment 7 Hari Terakhir (s/d {{ \Carbon\Carbon::parse($enrollmentDate)->translatedFormat("d M Y") }})'
    };

    const chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hourlyData.labels,
            datasets: [
                {
                    label: 'Data Sesuai (Ada Pemilik)',
                    data: hourlyData.matched,
                    backgroundColor: '#0D9488',
                    borderRadius: 4,
                    maxBarThickness: 28
                },
                {
                    label: 'Belum Ditautkan',
                    data: hourlyData.unassigned,
                    backgroundColor: '#F59E0B',
                    borderRadius: 4,
                    maxBarThickness: 28
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        footer: function (tooltipItems) {
                            let total = 0;
                            tooltipItems.forEach(function (item) {
                                total += item.parsed.y;
                            });
                            return 'Total Enroll: ' + total + ' Perangkat';
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 12
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.15)'
                    }
                }
            }
        }
    });

    const btnHourly = document.getElementById('btnChartHourly');
    const btnDaily = document.getElementById('btnChartDaily');
    const subtitleEl = document.getElementById('chartSubtitle');

    function switchChartMode(source) {
        chartInstance.data.labels = source.labels;
        chartInstance.data.datasets[0].data = source.matched;
        chartInstance.data.datasets[1].data = source.unassigned;
        chartInstance.update();
        if (subtitleEl) {
            subtitleEl.textContent = source.subtitle;
        }
    }

    if (btnHourly && btnDaily) {
        btnHourly.addEventListener('click', function () {
            btnHourly.classList.add('active');
            btnDaily.classList.remove('active');
            switchChartMode(hourlyData);
        });

        btnDaily.addEventListener('click', function () {
            btnDaily.classList.add('active');
            btnHourly.classList.remove('active');
            switchChartMode(dailyData);
        });
    }
});
</script>
@endpush
@endsection
