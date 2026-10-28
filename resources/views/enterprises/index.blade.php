@extends('layouts.app')

@section('title', 'Kelola Enterprise')
@section('page_title', 'Kelola Enterprise Google AMAPI')

@section('content')
<!-- Header & Quick Actions -->
<div class="row g-3 mb-4">
    <!-- Card Status Integrasi Master Google Cloud -->
    <div class="col-12 col-lg-7">
        <div class="card card-mdm border-0 h-100 p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-teal bg-opacity-10 text-teal p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-cloud-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Platform Master Google Cloud</h6>
                        <span class="text-secondary small">Kredensial Service Account terpusat untuk operasional EMM</span>
                    </div>
                </div>
                @if($connectionTest['success'] ?? false)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle font-mono px-3 py-1 fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i>AMAPI CONNECTED
                    </span>
                @else
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle font-mono px-3 py-1 fs-6">
                        <i class="bi bi-x-circle-fill me-1"></i>TERPUTUS
                    </span>
                @endif
            </div>

            <div class="row g-2 mb-3 small">
                <div class="col-sm-6">
                    <span class="text-secondary d-block">Google Cloud Project ID:</span>
                    <strong class="font-mono text-dark">{{ $projectId ?? '-' }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-secondary d-block">Service Account Client:</span>
                    <span class="font-mono text-secondary text-truncate d-block" style="font-size: 11px;" title="{{ $serviceAccountEmail }}">
                        {{ $serviceAccountEmail ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                <form action="{{ route('enterprises.test') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Uji Koneksi AMAPI</span>
                    </button>
                </form>

                <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadCredentialsModal">
                    <i class="bi bi-upload"></i>
                    <span>Ganti File JSON</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Card Enterprise Aktif -->
    <div class="col-12 col-lg-5">
        <div class="card card-mdm border-0 h-100 p-4 bg-teal bg-opacity-10 border border-teal-subtle">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-teal text-uppercase fw-bold small" style="font-size: 11px; letter-spacing: 0.5px;">
                    <i class="bi bi-patch-check-fill me-1"></i>Enterprise Aktif Digunakan
                </span>
                <span class="badge bg-teal text-white font-mono px-2 py-1">DEFAULT</span>
            </div>

            <h4 class="fw-bold text-dark mb-1">{{ $activeEnterprise->name ?? 'Belum Ada' }}</h4>
            <div class="font-mono text-secondary small mb-3">
                <i class="bi bi-key-fill text-teal me-1"></i>{{ $activeEnterprise->enterprise_id ?? '-' }}
            </div>

            <div class="mt-auto pt-2 border-top border-teal-subtle d-flex justify-content-between align-items-center small text-secondary">
                <span>Kontak: {{ $activeEnterprise->admin_email ?? '-' }}</span>
                <span class="font-mono">{{ $activeEnterprise->admin_phone ?? '' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Table: Daftar Enterprise -->
<div class="card card-mdm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Daftar Akun Enterprise ({{ $enterprises->count() }})</h5>
            <span class="text-secondary small">Kelola akun Enterprise Google Play, alihkan unit aktif, atau daftarkan akun baru secara otomatis</span>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#signupEnterpriseModal">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Daftarkan Enterprise Baru (Otomatis)</span>
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-tosca">
                <tr>
                    <th scope="col" class="ps-4" style="width: 50px;">No</th>
                    <th scope="col">Nama Enterprise</th>
                    <th scope="col">Enterprise ID (AMAPI)</th>
                    <th scope="col">Email & Kontak Admin</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tanggal Didaftarkan</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enterprises as $index => $ent)
                <tr class="{{ $ent->is_active ? 'bg-teal bg-opacity-10' : '' }}">
                    <td class="ps-4 text-secondary small">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-building text-teal"></i>
                            <span>{{ $ent->name }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-dark border font-mono fs-6 py-1 px-2">
                            {{ $ent->enterprise_id }}
                        </span>
                    </td>
                    <td>
                        <div class="text-dark small">{{ $ent->admin_email ?: '-' }}</div>
                        @if($ent->admin_phone)
                            <div class="text-secondary font-mono" style="font-size: 11px;">{{ $ent->admin_phone }}</div>
                        @endif
                    </td>
                    <td>
                        @if($ent->is_active)
                            <span class="badge bg-success d-inline-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-check-circle-fill"></i> AKTIF (DIGUNAKAN)
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border">
                                STANDBY
                            </span>
                        @endif
                    </td>
                    <td class="text-secondary small">
                        {{ $ent->created_at ? $ent->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex align-items-center gap-1">
                            <a href="{{ route('enterprises.show', $ent->id) }}" class="btn btn-sm btn-outline-teal py-1 px-2" title="Lihat Detail & Daftar Policies">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>

                            @if($ent->is_active)
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle py-1 px-2 small">
                                    <i class="bi bi-star-fill me-1"></i>Aktif
                                </span>
                            @else
                                <form action="{{ route('enterprises.activate', $ent->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Alihkan Enterprise aktif ke {{ $ent->name }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Jadikan Enterprise Aktif">
                                        <i class="bi bi-check2-circle me-1"></i>Aktifkan
                                    </button>
                                </form>

                                <form action="{{ route('enterprises.destroy', $ent->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus Enterprise {{ $ent->name }} ({{ $ent->enterprise_id }})? Aksi ini akan menghapus akun enterprise dari sistem dan Google AMAPI.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus Enterprise">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-building-x text-muted fs-1 d-block mb-2"></i>
                        Belum ada akun Enterprise yang tersimpan. Klik tombol <strong>Daftarkan Enterprise Baru</strong> di atas untuk membuat akun otomatis.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Informasi Alur Otomatisasi Google Sign-Up -->
<div class="card card-mdm border-0 p-4 bg-light">
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-info-circle-fill text-teal fs-5"></i>
        <h6 class="fw-bold mb-0 text-dark">Cara Kerja Otomatisasi Pendaftaran Enterprise (Sign-up URL Flow)</h6>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 bg-white rounded border h-100">
                <div class="fw-bold text-teal mb-1">1. Klik Daftarkan Enterprise</div>
                <p class="text-secondary small mb-0">
                    Dashboard secara otomatis membuat token pendaftaran resmi ke Google AMAPI melalui Master Service Account.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded border h-100">
                <div class="fw-bold text-teal mb-1">2. Konfirmasi di Google Play</div>
                <p class="text-secondary small mb-0">
                    Anda login dengan akun Google (Gmail biasa / Google Workspace), masukkan nama perusahaan/cabang, dan setujui perjanjian.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded border h-100">
                <div class="fw-bold text-teal mb-1">3. Otomatis Terhubung</div>
                <p class="text-secondary small mb-0">
                    Google otomatis mengembalikan Enterprise ID baru ke dashboard Anda tanpa perlu membuka Google Cloud Console.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Konfirmasi Pendaftaran Enterprise Baru -->
<div class="modal fade" id="signupEnterpriseModal" tabindex="-1" aria-labelledby="signupEnterpriseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('enterprises.signup') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom bg-teal text-white">
                    <h5 class="modal-title fw-bold" id="signupEnterpriseModalLabel">
                        <i class="bi bi-building-add me-2"></i>Daftarkan Enterprise Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-teal bg-opacity-10 border border-teal-subtle d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill text-teal fs-4 flex-shrink-0"></i>
                        <span class="small text-dark">Anda akan dialihkan sementara ke portal resmi Google Managed Play untuk konfirmasi nama organisasi.</span>
                    </div>

                    <p class="text-secondary small mb-3">
                        Langkah yang akan dijalankan sistem:
                    </p>
                    <ol class="text-secondary small mb-3 ps-3">
                        <li>Sistem menghasilkan tautan pendaftaran terenkripsi via Google Cloud Project <code>{{ $projectId }}</code>.</li>
                        <li>Anda mengisi nama organisasi/cabang baru di portal Google.</li>
                        <li>Google mengalihkan kembali ke dashboard ini dengan <strong>Enterprise ID baru</strong> yang langsung siap dipakai.</li>
                    </ol>

                    <p class="text-muted small fst-italic mb-0">
                        Tidak diperlukan akses Google Cloud Console atau download file JSON tambahan.
                    </p>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-box-arrow-up-right"></i> Lanjutkan ke Google Sign-Up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Upload Service Account JSON -->
<div class="modal fade" id="uploadCredentialsModal" tabindex="-1" aria-labelledby="uploadCredentialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('enterprises.upload-credentials') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="uploadCredentialsModalLabel">
                        <i class="bi bi-filetype-json text-teal me-2"></i>Perbarui Master Service Account JSON
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="credentials_file" class="form-label small fw-semibold">Pilih File Kredensial (.json)</label>
                        <input type="file" name="credentials_file" id="credentials_file" class="form-control" accept=".json,application/json" required>
                        <span class="form-text small text-secondary">
                            Unduh kunci JSON dari <em>Google Cloud Console &gt; IAM &amp; Admin &gt; Service Accounts &gt; Keys</em>.
                        </span>
                    </div>

                    <div class="alert alert-warning bg-warning bg-opacity-10 border border-warning-subtle small mb-0">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                        Mengganti file kredensial akan mengubah akun Google Cloud master yang digunakan dashboard untuk berkomunikasi dengan Google AMAPI.
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal btn-sm fw-semibold">Simpan Kredensial Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
