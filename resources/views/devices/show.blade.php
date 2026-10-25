@extends('layouts.app')

@section('title', 'Detail Perangkat: ' . $device->human_model_name)
@section('page_title', 'Detail & Kontrol Perangkat')

@section('content')
<!-- Back Button & Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Daftar">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">{{ $device->human_model_name }}</h4>
            <span class="font-mono text-muted small">{{ $device->device_id }}</span>
        </div>
    </div>
    <div>
        @if($device->state === 'ACTIVE')
            <span class="badge badge-compliant fs-6">
                <i class="bi bi-check-circle-fill me-1"></i> ACTIVE
            </span>
        @elseif($device->state === 'DELETED')
            <span class="badge bg-secondary fs-6">
                <i class="bi bi-archive-fill me-1"></i> DI-UNENROLL (DELETED)
            </span>
        @else
            <span class="badge badge-warning-custom fs-6">
                <i class="bi bi-exclamation-circle me-1"></i> {{ $device->state }}
            </span>
        @endif
    </div>
</div>

@if($device->state === 'DELETED')
<!-- Notice for Unenrolled Device -->
<div class="card card-mdm border-0 p-3 mb-4 bg-light">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-secondary bg-opacity-10 p-2 text-secondary">
                <i class="bi bi-archive-fill fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Perangkat Telah Di-unenroll / Dihapus</h6>
                <span class="text-secondary small">Perangkat ini sudah tidak aktif di Google Cloud AMAPI. Seluruh perintah remote dinonaktifkan. Data ini disimpan sebagai arsip histori.</span>
            </div>
        </div>
        @if(auth()->user()->hasPermission('devices.delete'))
        <form action="{{ route('devices.destroy-permanent', $device->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus data perangkat ini secara permanen dari database?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                <i class="bi bi-trash"></i> Hapus Permanen dari Database
            </button>
        </form>
        @endif
    </div>
</div>
@elseif(auth()->user()->hasPermission(['devices.command', 'devices.delete']) || $device->policy_id === 'Lunas')
<!-- Remote Command Quick Action Toolbar -->
<div class="card card-mdm border-0 p-3 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            @if($device->policy_id === 'Lunas')
                <span class="badge bg-success py-2 px-3 d-inline-flex align-items-center gap-1 font-mono fs-6 shadow-sm">
                    <i class="bi bi-patch-check-fill"></i> STATUS: LUNAS / BEBAS KONTROL
                </span>
            @else
                <span class="fw-semibold text-secondary small text-uppercase" style="font-size: 11px;">Perintah Jarak Jauh:</span>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($device->policy_id !== 'Lunas' && auth()->user()->hasPermission('devices.command'))
            <!-- Tombol Lepaskan Perangkat (Lunas) -->
            <button type="button" class="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#releaseLunasModal">
                <i class="bi bi-patch-check-fill"></i>
                <span>Lepaskan Perangkat (Lunas)</span>
            </button>
            @endif

            @if(auth()->user()->hasPermission('devices.command'))
            <!-- 1. Lock Screen -->
            <form action="{{ route('devices.command', $device->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="command_type" value="LOCK">
                <button type="submit" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" onclick="return confirm('Kunci layar perangkat ini sekarang?')">
                    <i class="bi bi-lock"></i>
                    <span>Kunci Layar</span>
                </button>
            </form>

            <!-- 2. Reboot -->
            <form action="{{ route('devices.command', $device->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="command_type" value="REBOOT">
                <button type="submit" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" onclick="return confirm('Yakin ingin mereboot perangkat ini secara remote?')">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Reboot</span>
                </button>
            </form>

            <!-- 3. Reset PIN Modal Trigger -->
            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#resetPinModal">
                <i class="bi bi-key"></i>
                <span>Reset PIN</span>
            </button>

            <!-- 4. Switch Policy Modal Trigger -->
            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#switchPolicyModal">
                <i class="bi bi-shield-shaded"></i>
                <span>Ubah Policy</span>
            </button>
            @endif

            <!-- 5. Unenroll Modal Trigger (Superadmin Only) -->
            @if(auth()->user()->isAdmin())
            <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#unenrollModal">
                <i class="bi bi-trash3"></i>
                <span>Unenroll</span>
            </button>
            @endif
        </div>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <!-- Left Column: Hardware & System Specs -->
    <div class="col-12 col-lg-6">
        <div class="card card-mdm border-0 h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-cpu text-teal me-2"></i>Spesifikasi Hardware & Sistem
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-secondary fw-normal w-40">Nama Komersial</th>
                                <td class="fw-bold text-dark fs-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>{{ $device->human_model_name }}</span>
                                        @if(auth()->user()->hasPermission('devices.edit'))
                                        <button type="button" class="btn btn-sm btn-outline-teal py-0 px-2" data-bs-toggle="modal" data-bs-target="#editModelNameModal" title="Ubah Nama Komersial">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Kode Model Pabrik</th>
                                <td><span class="font-mono text-dark">{{ $device->hardware_model ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Pabrikan / Brand</th>
                                <td class="text-dark">{{ ucfirst($device->manufacturer ?? $device->brand ?? '-') }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Nomor Seri (Serial)</th>
                                <td><span class="device-serial text-dark fw-bold">{{ $device->serial_number ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Nomor IMEI</th>
                                <td>
                                    @if($device->imei)
                                        <span class="device-imei text-dark fw-bold">{{ $device->imei }}</span>
                                    @elseif($device->is_work_profile)
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle font-mono">
                                            <i class="bi bi-shield-lock me-1"></i>Dibatasi Google (Mode Work Profile)
                                        </span>
                                    @else
                                        <span class="text-muted small fst-italic">
                                            <i class="bi bi-clock-history me-1"></i>Menunggu sinkronisasi telemetri AMAPI
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Kartu SIM Terpasang</th>
                                <td>
                                    @php $simCards = $device->sim_cards; @endphp
                                    @if(!empty($simCards))
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($simCards as $sim)
                                                <div class="p-2 bg-light rounded-2 border d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono" style="font-size: 10.5px;">
                                                                <i class="bi bi-sim-fill me-1"></i>SIM {{ $sim['slot'] }}
                                                            </span>
                                                            @if($sim['phone_number'])
                                                                <span class="fw-bold text-dark font-mono">{{ $sim['phone_number'] }}</span>
                                                            @else
                                                                <span class="text-secondary small fst-italic">Nomor tidak tersimpan di chip SIM</span>
                                                            @endif
                                                            <span class="badge bg-white text-secondary border small" style="font-size: 10.5px;">
                                                                {{ $sim['carrier_name'] }}
                                                            </span>
                                                        </div>
                                                        @if($sim['icc_id'])
                                                            <div class="text-muted font-mono mt-1" style="font-size: 10.5px;">
                                                                ICCID: {{ $sim['icc_id'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @if($sim['phone_number'])
                                                        <div class="d-flex align-items-center gap-1">
                                                            <a href="tel:{{ $sim['phone_number'] }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Telepon Nomor SIM {{ $sim['slot'] }}">
                                                                <i class="bi bi-telephone-fill"></i>
                                                            </a>
                                                            @if($sim['whatsapp_phone'])
                                                                <a href="https://wa.me/{{ $sim['whatsapp_phone'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success py-0 px-2" title="Chat WhatsApp Nomor SIM {{ $sim['slot'] }}">
                                                                    <i class="bi bi-whatsapp"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">
                                            <i class="bi bi-sim me-1"></i>Belum ada data kartu SIM terdeteksi dari perangkat
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Jenis Pendaftaran (Mode)</th>
                                <td>
                                    @if($device->is_work_profile)
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle font-mono">
                                            <i class="bi bi-person-workspace me-1"></i>PROFILE_OWNER (Work Profile / Pribadi)
                                        </span>
                                        <div class="small text-danger mt-1">
                                            <i class="bi bi-info-circle me-1"></i>Google memblokir pembacaan IMEI pada mode Work Profile. Reset & scan saat setup wizard awal untuk beralih ke Fully Managed.
                                        </div>
                                    @else
                                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-mono">
                                            <i class="bi bi-building-check me-1"></i>DEVICE_OWNER (Fully Managed / Perusahaan)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Kepemilikan (Ownership)</th>
                                <td>
                                    <span class="badge bg-light text-dark border font-mono">
                                        {{ $device->ownership }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Versi Android OS</th>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-android2 text-success me-1"></i>Android {{ $device->android_version ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Status Keaktifan</th>
                                <td>
                                    @if($device->is_online)
                                        <span class="badge badge-compliant font-mono">
                                            <i class="bi bi-broadcast me-1"></i>Aktif Online (Terhubung)
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle font-mono">
                                            <i class="bi bi-dash-circle-fill me-1"></i>Offline (Terakhir lapor: {{ $device->last_status_report ? $device->last_status_report->diffForHumans() : 'Belum pernah' }})
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Status Daya & Baterai</th>
                                <td>
                                    @if($device->battery_level !== null)
                                        <span class="badge {{ $device->battery_badge_class }} font-mono fs-6 py-1 px-3">
                                            <i class="bi {{ $device->battery_icon }} me-1"></i>{{ $device->battery_level }}%
                                        </span>
                                    @else
                                        <span class="text-muted small fst-italic">
                                            <i class="bi bi-battery me-1"></i>Telemetri daya aktif (Menunggu siklus lapor perangkat)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Mode Lokasi & GPS</th>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle font-mono">
                                        <i class="bi bi-geo-alt-fill text-teal me-1"></i>HIGH_ACCURACY (GPS Dikunci Aktif oleh Enterprise)
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Terakhir Lapor Status</th>
                                <td class="text-secondary small">
                                    @if($device->last_status_report)
                                        <span class="fw-semibold text-dark font-mono">{{ $device->last_status_report->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB</span>
                                        <span class="text-muted ms-1">({{ $device->last_status_report->diffForHumans() }})</span>
                                    @else
                                        Belum pernah
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Waktu Pendaftaran</th>
                                <td class="text-secondary small">
                                    @if($device->enrolled_at)
                                        <span class="font-mono text-dark">{{ $device->enrolled_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Policy Aktif</th>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        @if($device->policy_id === 'Lunas')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle font-mono fs-6 py-1 px-3">
                                                <i class="bi bi-patch-check-fill me-2"></i>Lunas (Bebas Kontrol MDM)
                                            </span>
                                        @else
                                            <span class="badge-policy font-mono fs-6 py-1 px-3">
                                                <i class="bi bi-shield-check me-2 text-teal"></i>
                                                {{ $device->policy ? $device->policy->name : ($device->policy_id ?? 'default') }}
                                            </span>
                                        @endif
                                        @if(auth()->user()->hasPermission('devices.command'))
                                        <button type="button" class="btn btn-sm btn-outline-teal py-0 px-2" data-bs-toggle="modal" data-bs-target="#switchPolicyModal" title="Ubah Policy">
                                            <i class="bi bi-pencil-square me-1"></i> Ubah
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Cabang Operasional</th>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        @if($device->branch)
                                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-6 py-1 px-3">
                                                <i class="bi bi-geo-alt-fill me-1"></i>
                                                {{ $device->branch->name }} ({{ $device->branch->code }})
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border font-mono">
                                                Pusat / Belum Ditempatkan
                                            </span>
                                        @endif

                                        @if(!auth()->user()->isBranchScoped() && auth()->user()->hasPermission(['branches.manage', 'devices.edit']))
                                        <button type="button" class="btn btn-sm btn-outline-teal py-0 px-2" data-bs-toggle="modal" data-bs-target="#transferBranchModal" title="Pindahkan Cabang">
                                            <i class="bi bi-arrow-left-right me-1"></i> Mutasi Cabang
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Ownership & Member Info -->
    <div class="col-12 col-lg-6">
        <div class="card card-mdm border-0 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-badge text-teal me-2"></i>Informasi Pemilik / Anggota
                </h6>
                @if(auth()->user()->hasPermission('devices.edit'))
                <button type="button" class="btn btn-sm btn-outline-teal" data-bs-toggle="modal" data-bs-target="#editOwnerModal">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                @endif
            </div>
            <div class="card-body p-4">
                @if($device->owner)
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-secondary fw-normal w-40">Nama Pemilik</th>
                                <td class="fw-bold fs-6 text-dark">{{ $device->owner->nama }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Status Penggunaan</th>
                                <td>
                                    @if($device->is_online)
                                        <span class="badge badge-compliant font-mono">
                                            <i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i>Aktif Menggunakan (Online)
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle font-mono">
                                            <i class="bi bi-circle-fill text-danger me-1" style="font-size: 8px;"></i>Tidak Aktif / Offline
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">ID Anggota / Agen</th>
                                <td><span class="badge bg-light text-dark border font-mono fs-6">{{ $device->owner->id_anggota }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Nomor Telepon</th>
                                <td>
                                    @if($device->owner->telepon)
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <a href="tel:{{ $device->owner->telepon }}" class="font-mono text-decoration-none fw-semibold">
                                            <i class="bi bi-telephone me-1"></i>{{ $device->owner->telepon }}
                                        </a>
                                        @php $waOwner = \App\Models\Device::formatWhatsappPhone($device->owner->telepon); @endphp
                                        @if($waOwner)
                                            <a href="https://wa.me/{{ $waOwner }}" target="_blank" rel="noopener" class="badge bg-success bg-opacity-10 text-success border border-success-subtle text-decoration-none">
                                                <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                            </a>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif

                                    @php $detectedPhones = $device->detected_phone_numbers; @endphp
                                    @if(!empty($detectedPhones))
                                        <div class="mt-2 p-2 bg-teal-subtle rounded-2 border border-teal-subtle small">
                                            <div class="fw-semibold text-teal mb-1" style="font-size: 11px;">
                                                <i class="bi bi-sim-fill me-1"></i>Nomor SIM Terdeteksi di HP:
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($detectedPhones as $simNum)
                                                    @php $waSim = \App\Models\Device::formatWhatsappPhone($simNum); @endphp
                                                    <span class="badge bg-white text-dark border font-mono d-inline-flex align-items-center gap-2 py-1 px-2">
                                                        <span>{{ $simNum }}</span>
                                                        @if($waSim)
                                                            <a href="https://wa.me/{{ $waSim }}" target="_blank" rel="noopener" class="text-success text-decoration-none" title="Hubungi via WhatsApp">
                                                                <i class="bi bi-whatsapp"></i>
                                                            </a>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Departemen / Wilayah</th>
                                <td class="text-dark">{{ $device->owner->departemen ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary fw-normal">Catatan Tambahan</th>
                                <td class="text-secondary small">{{ $device->owner->catatan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-secondary">
                    <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                    Perangkat ini belum ditautkan ke data pemilik/anggota manapun.
                    @if(auth()->user()->hasPermission('devices.edit'))
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-teal" data-bs-toggle="modal" data-bs-target="#editOwnerModal">
                            <i class="bi bi-plus-circle me-1"></i>Tautkan Pemilik Sekarang
                        </button>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Real-Time GPS Location & Interactive Map Card -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-geo-alt-fill text-teal"></i>
                <span>Pelacakan Lokasi GPS Perangkat (Real-Time Maps)</span>
                @if($device->has_location)
                    <span class="badge badge-compliant font-mono" style="font-size: 11px;">
                        <i class="bi bi-crosshair me-1"></i>Terkunci GPS
                    </span>
                @endif
            </h6>
            <span class="text-secondary small">
                @if($device->has_location)
                    Diperbarui: <strong>{{ $device->location_updated_at ? $device->location_updated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB (' . $device->location_updated_at->diffForHumans() . ')' : '-' }}</strong>
                    @if($device->location_accuracy)
                        • Akurasi ±{{ round($device->location_accuracy) }} meter
                    @endif
                @else
                    Menunggu kiriman titik koordinat dari aplikasi <strong>Amanin Location Service</strong> di perangkat
                @endif
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($device->has_location)
                <span class="badge bg-light text-dark border font-mono py-2 px-3">
                    {{ number_format($device->latitude, 6) }}, {{ number_format($device->longitude, 6) }}
                </span>
                <a href="{{ $device->google_maps_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-teal d-flex align-items-center gap-1">
                    <i class="bi bi-map-fill"></i>
                    <span>Buka di Google Maps</span>
                </a>
            @else
                <a href="{{ route('apps.search') }}" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                    <span>Pasang APK Pelacak Lokasi</span>
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        @if($device->has_location)
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
            <div id="deviceLocationMap" style="height: 360px; width: 100%; z-index: 1;"></div>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var lat = {{ (float) $device->latitude }};
                    var lng = {{ (float) $device->longitude }};
                    var acc = {{ (float) ($device->location_accuracy ?? 25) }};
                    var map = L.map('deviceLocationMap').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);
                    var marker = L.marker([lat, lng]).addTo(map);
                    marker.bindPopup(
                        "<strong>{{ addslashes($device->owner->nama ?? $device->human_model_name) }}</strong><br>" +
                        "<span style='font-size:12px;'>{{ addslashes($device->human_model_name) }} ({{ addslashes($device->imei ?? '-') }})</span><br>" +
                        "<span style='font-size:11px;color:#0D9488;'>Koordinat: " + lat.toFixed(6) + ", " + lng.toFixed(6) + "</span>"
                    ).openPopup();
                    if (acc > 0) {
                        L.circle([lat, lng], {
                            color: '#0D9488',
                            fillColor: '#0D9488',
                            fillOpacity: 0.12,
                            radius: acc
                        }).addTo(map);
                    }
                });
            </script>
        @else
            <div class="p-4 text-center text-secondary bg-light bg-opacity-50">
                <i class="bi bi-geo-alt text-muted fs-2 d-block mb-2"></i>
                <div class="fw-semibold text-dark mb-1">Titik Koordinat GPS Belum Masuk</div>
                <p class="small text-secondary mb-0 mx-auto" style="max-width: 620px;">
                    Pastikan aplikasi <strong>Amanin Location Service (<code>com.amanin.mdm.locationagent</code>)</strong> sudah diunggah ke tab <strong>Aplikasi Pribadi (Private Apps)</strong> dan dipasang sebagai <code>FORCE_INSTALLED</code> pada Policy perangkat ini.
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Command History Audit Table -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-clock-history text-teal me-2"></i>Riwayat Perintah Jarak Jauh (Audit Log)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4">Waktu Eksekusi</th>
                    <th scope="col">Jenis Perintah</th>
                    <th scope="col">Operator</th>
                    <th scope="col">Status</th>
                    <th scope="col">AMAPI Command ID</th>
                    <th scope="col" class="pe-4">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($device->commands as $cmd)
                <tr>
                    <td class="ps-4 text-secondary small font-mono">
                        {{ $cmd->created_at->format('d M Y H:i:s') }}
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border font-mono">
                            {{ $cmd->command_type }}
                        </span>
                    </td>
                    <td>
                        {{ $cmd->issuedBy->name ?? $cmd->issuedBy->username ?? 'System' }}
                    </td>
                    <td>
                        @if($cmd->status === 'SUCCESS')
                            <span class="badge badge-compliant"><i class="bi bi-check-circle-fill"></i> SUKSES</span>
                        @elseif($cmd->status === 'PENDING')
                            <span class="badge badge-pending-custom"><i class="bi bi-hourglass-split"></i> PENDING</span>
                        @else
                            <span class="badge badge-lost-mode"><i class="bi bi-x-circle-fill"></i> GAGAL</span>
                        @endif
                    </td>
                    <td>
                        <span class="amapi-path text-muted small">
                            {{ $cmd->amapi_command_id ? basename($cmd->amapi_command_id) : '-' }}
                        </span>
                    </td>
                    <td class="pe-4 text-secondary small">
                        {{ $cmd->error_message ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted small">
                        Belum ada riwayat perintah yang dikirimkan ke perangkat ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODALS -->

<!-- 1. Reset PIN Modal -->
<div class="modal fade" id="resetPinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('devices.command', $device->id) }}" method="POST">
                @csrf
                <input type="hidden" name="command_type" value="RESET_PASSWORD">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Reset PIN / Password Perangkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Perintah ini akan mengubah PIN layar kunci perangkat secara remote dan langsung memaksa penguncian layar.
                    </p>
                    <div class="mb-3">
                        <label for="new_password" class="form-label small fw-semibold">PIN / Password Baru</label>
                        <input type="text" name="new_password" id="new_password" class="form-control font-mono" placeholder="Contoh: 123456" required>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Kirim Perintah Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Switch Policy Modal -->
<div class="modal fade" id="switchPolicyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('devices.policy', $device->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Ubah Policy Perangkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="policy_id" class="form-label small fw-semibold">Pilih Policy Baru</label>
                        <select name="policy_id" id="policy_id" class="form-select" required>
                            @foreach($allPolicies as $pol)
                            <option value="{{ $pol->policy_id }}" {{ $device->policy_id == $pol->policy_id ? 'selected' : '' }}>
                                {{ $pol->name }} ({{ $pol->policy_id }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Terapkan Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. Edit Owner Modal -->
<div class="modal fade" id="editOwnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('owners.store') }}" method="POST">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->device_id }}">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Data Pemilik / Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-control" value="{{ $device->owner->nama ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_anggota" class="form-label small fw-semibold">ID Anggota / Agen</label>
                        <input type="text" name="id_anggota" id="id_anggota" class="form-control font-mono" value="{{ $device->owner->id_anggota ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label small fw-semibold">Nomor Telepon</label>
                        <input type="text" name="telepon" id="telepon" class="form-control font-mono" value="{{ $device->owner->telepon ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="departemen" class="form-label small fw-semibold">Departemen / Wilayah</label>
                        <input type="text" name="departemen" id="departemen" class="form-control" value="{{ $device->owner->departemen ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label small fw-semibold">Catatan</label>
                        <textarea name="catatan" id="catatan" class="form-control" rows="2">{{ $device->owner->catatan ?? '' }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal btn-sm">Simpan Data Pemilik</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pelepasan Perangkat (Lunas) -->
@if($device->state !== 'DELETED' && $device->policy_id !== 'Lunas')
<div class="modal fade" id="releaseLunasModal" tabindex="-1" aria-labelledby="releaseLunasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('devices.release', $device->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom bg-success bg-opacity-10 py-3">
                    <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2" id="releaseLunasModalLabel">
                        <i class="bi bi-patch-check-fill"></i>
                        <span>Konfirmasi Pelepasan Perangkat (Lunas)</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
                        <div class="small">
                            Nasabah <strong>{{ $device->owner?->nama ?? 'Nasabah' }}</strong> (Model: <strong>{{ $device->human_model_name }}</strong>) telah menyelesaikan kewajiban/cicilan perangkat.
                        </div>
                    </div>

                    <p class="text-secondary small mb-3">
                        Dengan melepaskan perangkat ke status <strong>Lunas</strong>:
                    </p>
                    <ul class="text-secondary small mb-3 ps-3">
                        <li><strong class="text-success"><i class="bi bi-check-circle me-1"></i>Data Pribadi Aman:</strong> Foto, chat WhatsApp, dan aplikasi nasabah <strong>TIDAK AKAN DIHAPUS</strong>.</li>
                        <li><strong class="text-success"><i class="bi bi-check-circle me-1"></i>Pelacak Lokasi Nonaktif:</strong> Aplikasi agen pelacak dicabut dari status wajib dan berhenti melacak.</li>
                        <li><strong class="text-success"><i class="bi bi-check-circle me-1"></i>FRP Kantor Dicabut:</strong> Proteksi reset kantor dinonaktifkan, nasabah bebas reset pabrik sendiri tanpa terkunci akun kantor.</li>
                        <li><strong class="text-success"><i class="bi bi-check-circle me-1"></i>Bebas Batasan:</strong> Semua kunci layar tagihan dan pembatasan operasional dinonaktifkan.</li>
                    </ul>

                    <p class="text-muted small fst-italic mb-0">
                        Apakah Anda yakin ingin melepaskan perangkat ini sekarang?
                    </p>
                </div>
                <div class="modal-footer border-top bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-patch-check-fill"></i> Ya, Lepaskan Perangkat (Lunas)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->isAdmin())
<!-- 5. Unenroll Double Confirmation Modal (Superadmin Only) -->
<div class="modal fade" id="unenrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('devices.unenroll', $device->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header border-bottom bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus / Unenroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-danger fw-semibold small mb-2">
                        PERINGATAN: Tindakan ini akan menghapus perangkat dari pengelolaan enterprise dan melakukan factory reset/wipe!
                    </p>
                    <p class="text-secondary small">
                        Ketik <strong class="text-dark">UNENROLL</strong> pada kolom di bawah ini untuk mengonfirmasi:
                    </p>
                    <input type="text" id="confirmText" class="form-control font-mono" placeholder="Ketik UNENROLL" oninput="document.getElementById('btnUnenrollSubmit').disabled = (this.value !== 'UNENROLL')">
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnUnenrollSubmit" class="btn btn-danger btn-sm" disabled>
                        Ya, Unenroll Perangkat Ini
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- 6. Edit Marketing Model Name Modal -->
<div class="modal fade" id="editModelNameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('devices.rename-model', $device->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-teal me-2"></i>Ubah Nama Komersial Model</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Kode Model Pabrik</label>
                        <input type="text" class="form-control bg-light font-mono" value="{{ $device->hardware_model ?? '-' }}" readonly>
                        <span class="text-muted small" style="font-size: 11px;">Kode model hardware bawaan dari Google AMAPI</span>
                    </div>

                    <div class="mb-3">
                        <label for="marketing_name" class="form-label small fw-semibold text-secondary">Nama Komersial / Tipe HP <span class="text-danger">*</span></label>
                        <input type="text" name="marketing_name" id="marketing_name" class="form-control" value="{{ $device->marketing_name ?? $device->human_model_name }}" placeholder="Contoh: Vivo Y29 5G, Samsung Galaxy A55" required>
                    </div>

                    <div class="form-check p-3 bg-light rounded border">
                        <input class="form-check-input" type="checkbox" name="apply_to_all_same_model" value="1" id="applyAllCheck" checked>
                        <label class="form-check-label small text-dark fw-semibold" for="applyAllCheck">
                            Terapkan ke seluruh perangkat lain dengan kode tipe ini ({{ $device->hardware_model }})
                        </label>
                        <span class="text-muted d-block small" style="font-size: 11px;">Otomatis memperbarui nama di semua perangkat sejenis di dashboard</span>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(!auth()->user()->isBranchScoped() && auth()->user()->hasPermission(['branches.manage', 'devices.edit']))
<!-- 7. Transfer Branch Modal -->
<div class="modal fade" id="transferBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('devices.branch', $device->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-building text-teal me-2"></i>Mutasi / Penempatan Cabang Perangkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Pindahkan pengelolaan perangkat ini ke cabang lain. Admin cabang yang dipilih akan memiliki akses penuh atas perangkat ini.
                    </p>
                    <div class="mb-3">
                        <label for="branch_id" class="form-label small fw-semibold">Pilih Cabang Tujuan</label>
                        <select name="branch_id" id="branch_id" class="form-select">
                            <option value="" {{ !$device->branch_id ? 'selected' : '' }}>
                                🏢 Pusat / Tanpa Cabang Spesifik
                            </option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $device->branch_id == $b->id ? 'selected' : '' }}>
                                📍 {{ $b->name }} ({{ $b->code }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Cabang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
