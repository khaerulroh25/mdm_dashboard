@extends('layouts.app')

@section('title', 'Google Play Store Enterprise')
@section('page_title', 'Google Play Store Enterprise')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('apps.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Katalog">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">Google Play Store Enterprise</h4>
                <span class="badge bg-teal text-white" style="font-size: 11px;">
                    <i class="bi bi-google-play me-1"></i> Managed Google Play
                </span>
            </div>
            <span class="text-secondary small">Jelajahi dan pilih aplikasi langsung dari Google Play Store resmi untuk didistribusikan ke perangkat managed</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('apps.download-location-apk') }}" class="btn btn-teal btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-download"></i>
            <span>Unduh APK Pelacak Lokasi (Private App)</span>
        </a>
        <a href="{{ route('apps.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-grid me-1"></i> Lihat Katalog Tersimpan
        </a>
    </div>
</div>

<!-- Panduan Singkat Upload Private APK Pelacak Lokasi -->
<div class="alert bg-teal-subtle border border-teal-subtle rounded-3 py-2 px-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="small text-dark">
        <i class="bi bi-geo-alt-fill text-teal me-1"></i>
        <strong>Cara Mengaktifkan Pelacak Lokasi GPS (Opsi 2):</strong>
        1) Klik tombol <strong>Unduh APK Pelacak Lokasi</strong> di kanan atas &rarr;
        2) Pada jendela Google Play di bawah, buka menu kiri <strong>Aplikasi Pribadi (Private Apps)</strong> lalu klik tombol <strong>(+)</strong> untuk upload file <code>amanin-location-agent.apk</code> &rarr;
        3) Setelah terbit (~5-10 menit), klik aplikasi tersebut dan pasang ke Policy <strong>Mode Kiosk</strong> sebagai <code>FORCE_INSTALLED</code>.
    </div>
</div>

@if($webToken)
    <!-- Google Play Official Embedded Iframe Card -->
    <div class="card card-mdm border-0 p-0 overflow-hidden shadow-sm" style="height: calc(100vh - 180px); min-height: 750px; border-radius: 12px; background: #fff;">
        <iframe id="managed-play-iframe"
                src="https://play.google.com/work/embedded/search?token={{ urlencode($webToken) }}"
                style="width: 100%; height: 100%; border: none;"
                allow="clipboard-write"
                title="Managed Google Play Enterprise Store">
        </iframe>
    </div>
@else
    <!-- Notice When HTTPS is Not Configured -->
    <div class="card card-mdm border-0 p-4 mb-4">
        <div class="d-flex gap-3 align-items-start">
            <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis flex-shrink-0">
                <i class="bi bi-shield-lock fs-1"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <h5 class="fw-bold text-dark mb-0">Iframe Resmi Google Play Memerlukan Koneksi HTTPS</h5>
                    <span class="badge bg-warning text-dark">Google Security Standard</span>
                </div>
                <p class="text-secondary mb-3">
                    Google Android Management API (AMAPI) mewajibkan protokol <strong>HTTPS</strong> yang valid untuk memuat jendela toko Managed Google Play (Google menolak <code>http://localhost</code> tanpa SSL).
                </p>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-globe text-primary me-1"></i> Menggunakan Tunneling HTTPS (ngrok):
                    </h6>
                    <p class="small text-muted mb-2">
                        Pastikan ngrok sedang berjalan dan buka dashboard melalui alamat HTTPS dari ngrok tersebut, misalnya:
                    </p>
                    <code class="d-block p-2 bg-white rounded border text-dark font-mono small mb-2">
                        https://substance-usual-exit.ngrok-free.dev/apps/search
                    </code>
                </div>

                @if($webTokenError)
                <div class="alert alert-danger small py-2 mb-0">
                    <strong>Google API Response:</strong> {{ $webTokenError }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- ========================================================================= -->
<!-- MODAL: ADD APP FROM IFRAME SELECTION -->
<!-- ========================================================================= -->
<div class="modal fade" id="iframeAppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-google-play text-teal"></i> Tambahkan Aplikasi ke MDM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="iframeAddAppForm" action="{{ route('apps.store') }}" method="POST">
                @csrf
                <input type="hidden" name="package_name" id="modal_package_name">
                <input type="hidden" name="app_name" id="modal_app_name">
                <input type="hidden" name="icon_url" id="modal_icon_url">
                <input type="hidden" name="developer" id="modal_developer">

                <div class="modal-body p-4">
                    <!-- Loading Spinner -->
                    <div id="modalLoading" class="text-center py-4">
                        <div class="spinner-border text-teal mb-2" role="status"></div>
                        <div class="text-secondary small">Mengambil informasi resmi dari Google Cloud AMAPI...</div>
                    </div>

                    <!-- App Details Preview -->
                    <div id="modalAppContent" style="display: none;">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3 border">
                            <img id="modal_preview_icon" src="" alt="App Icon" class="rounded-3 shadow-sm" style="width: 58px; height: 58px; object-fit: cover;">
                            <div class="overflow-hidden">
                                <h6 id="modal_preview_title" class="fw-bold text-dark text-truncate mb-1"></h6>
                                <span id="modal_preview_dev" class="text-secondary small d-block text-truncate"></span>
                                <span id="modal_preview_pkg" class="font-mono text-muted small d-block text-truncate"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_policy_id" class="form-label small fw-semibold text-dark">Pasang ke Policy (Opsional)</label>
                            <select name="policy_id" id="modal_policy_id" class="form-select">
                                <option value="">Hanya simpan di katalog</option>
                                @foreach($policies as $pol)
                                <option value="{{ $pol->policy_id }}">{{ $pol->name }} ({{ $pol->policy_id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="modal_install_type" class="form-label small fw-semibold text-dark">Tipe Instalasi di Perangkat</label>
                            <select name="install_type" id="modal_install_type" class="form-select">
                                <option value="AVAILABLE" selected>Tersedia di Play Store (AVAILABLE)</option>
                                <option value="FORCE_INSTALLED">Instal Otomatis Latar Belakang (FORCE_INSTALLED)</option>
                                <option value="KIOSK">Mode Kiosk Terkunci (KIOSK)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitIframeApp" class="btn btn-teal btn-sm fw-semibold" style="display: none;">
                        <i class="bi bi-check-lg me-1"></i> Simpan ke MDM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('iframeAppModal');
    const iframeModal = modalElement ? new bootstrap.Modal(modalElement) : null;

    // Listen to official Google Play postMessage events
    window.addEventListener('message', function (event) {
        // Security check: Origin MUST be Google Play
        if (event.origin !== 'https://play.google.com') {
            return;
        }

        console.log('Managed Google Play iframe event received:', event.data);

        let data = event.data;
        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (e) {
                // Ignore unparseable string
            }
        }

        if (!data) return;

        // Check if an app was selected: onproductselect, productSelected, etc.
        const action = data.action || data.type || data.event;
        const productId = data.productId || data.package_name || (data.product && data.product.productId);

        if (action === 'onproductselect' || productId) {
            handleAppSelected(productId || data.productId);
        }
    }, false);

    function handleAppSelected(packageName) {
        if (!packageName || !iframeModal) return;

        // Show modal and loading state
        document.getElementById('modalLoading').style.display = 'block';
        document.getElementById('modalAppContent').style.display = 'none';
        document.getElementById('btnSubmitIframeApp').style.display = 'none';
        iframeModal.show();

        // Fetch official details via Google AMAPI lookup
        fetch(`{{ route('apps.lookup') }}?package_name=${encodeURIComponent(packageName)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(res => {
            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('modalAppContent').style.display = 'block';
            document.getElementById('btnSubmitIframeApp').style.display = 'inline-block';

            if (res.success && res.app) {
                const app = res.app;
                document.getElementById('modal_package_name').value = app.package_name;
                document.getElementById('modal_app_name').value = app.app_name;
                document.getElementById('modal_icon_url').value = app.icon || '';
                document.getElementById('modal_developer').value = app.developer || '';

                document.getElementById('modal_preview_title').textContent = app.app_name;
                document.getElementById('modal_preview_dev').textContent = app.developer || 'Google Play Store';
                document.getElementById('modal_preview_pkg').textContent = app.package_name;
                document.getElementById('modal_preview_icon').src = app.icon || 'https://lh3.googleusercontent.com/QNmuZQc9I6Zbe3mWnSr0hycnENqGFCI5p3yE29Hkxtf22T0IWS6zTrpxULLyyjWpB7ONAXDsDQXnXcVWokl3eg';
            } else {
                // Fallback to basic package name
                document.getElementById('modal_package_name').value = packageName;
                document.getElementById('modal_app_name').value = packageName;
                document.getElementById('modal_preview_title').textContent = packageName;
                document.getElementById('modal_preview_dev').textContent = 'Google Play Store';
                document.getElementById('modal_preview_pkg').textContent = packageName;
            }
        })
        .catch(err => {
            console.error('Lookup error:', err);
            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('modalAppContent').style.display = 'block';
            document.getElementById('btnSubmitIframeApp').style.display = 'inline-block';

            document.getElementById('modal_package_name').value = packageName;
            document.getElementById('modal_app_name').value = packageName;
            document.getElementById('modal_preview_title').textContent = packageName;
            document.getElementById('modal_preview_dev').textContent = 'Google Play Store';
            document.getElementById('modal_preview_pkg').textContent = packageName;
        });
    }
});
</script>
@endpush
@endsection
