@extends('layouts.app')

@section('title', 'Pencarian Aplikasi Google Play')
@section('page_title', 'Google Play Store Enterprise')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('apps.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Katalog">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">Eksplorasi & Registrasi Google Play</h4>
            <span class="text-secondary small">Integrasi Resmi Google Android Management API (AMAPI)</span>
        </div>
    </div>
</div>

<!-- Nav Tabs: Official Iframe vs Fast AMAPI Search -->
<ul class="nav nav-pills nav-fill mb-4 bg-white p-2 rounded-3 shadow-sm border" id="playStoreTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ empty($keyword) ? 'active' : '' }} fw-semibold d-flex align-items-center justify-content-center gap-2 py-2" 
                id="iframe-tab" data-bs-toggle="tab" data-bs-target="#iframe-tab-pane" type="button" role="tab">
            <i class="bi bi-window text-teal fs-5"></i>
            <span>Managed Google Play (Iframe Resmi)</span>
            <span class="badge bg-teal text-white ms-1" style="font-size: 10px;">100% Google</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ !empty($keyword) ? 'active' : '' }} fw-semibold d-flex align-items-center justify-content-center gap-2 py-2" 
                id="search-tab" data-bs-toggle="tab" data-bs-target="#search-tab-pane" type="button" role="tab">
            <i class="bi bi-search text-teal fs-5"></i>
            <span>Pencarian Cepat & Package Name</span>
            @if(!empty($keyword))
                <span class="badge bg-primary text-white ms-1">{{ count($results) }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content" id="playStoreTabsContent">
    <!-- ========================================================================= -->
    <!-- TAB 1: MANAGED GOOGLE PLAY OFFICIAL IFRAME -->
    <!-- ========================================================================= -->
    <div class="tab-pane fade {{ empty($keyword) ? 'show active' : '' }}" id="iframe-tab-pane" role="tabpanel">
        @if($webToken)
            <div class="card card-mdm border-0 p-3 mb-3 bg-light-subtle">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="bi bi-shield-check me-1"></i> Sesi Enterprise Google Aktif
                        </span>
                        <span class="text-secondary small">Cari dan klik <strong>Pilih / Approve</strong> pada aplikasi resmi Google Play di bawah ini.</span>
                    </div>
                    <span class="text-muted small font-mono">Token: {{ substr($webToken, 0, 16) }}...</span>
                </div>
            </div>

            <!-- Google Play Official Iframe Container -->
            <div class="card card-mdm border-0 p-0 overflow-hidden shadow-sm" style="height: 800px; border-radius: 12px; background: #fff;">
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
                            Google Android Management API (AMAPI) menerapkan standar keamanan ketat untuk antarmuka enterprise: 
                            iframe resmi hanya diizinkan dimuat pada domain dengan protokol <strong>HTTPS</strong> yang valid (Google menolak <code>http://localhost</code> tanpa SSL).
                        </p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="bi bi-globe text-primary me-1"></i> Jika Anda Memiliki Domain / Tunneling:
                                    </h6>
                                    <p class="small text-muted mb-2">
                                        Gunakan tunneling HTTPS (misalnya <code>ngrok http 8000</code> atau Cloudflare Tunnel), lalu atur nilai URL tersebut di <code>.env</code>:
                                    </p>
                                    <code class="d-block p-2 bg-white rounded border text-dark font-mono small mb-2">
                                        AMAPI_PARENT_FRAME_URL=https://nama-tunnel-anda.ngrok-free.app/apps/search
                                    </code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="bi bi-lightning-charge text-warning me-1"></i> Solusi Cepat Tanpa HTTPS:
                                    </h6>
                                    <p class="small text-muted mb-3">
                                        Anda dapat langsung menggunakan <strong>Tab Pencarian Cepat & Package Name</strong> di atas. Fitur tersebut terhubung langsung ke backend Google AMAPI resmi tanpa memerlukan HTTPS!
                                    </p>
                                    <button type="button" class="btn btn-sm btn-teal" onclick="document.getElementById('search-tab').click();">
                                        <i class="bi bi-arrow-right-circle me-1"></i> Buka Pencarian Cepat Sekarang
                                    </button>
                                </div>
                            </div>
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
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: FAST SEARCH & PACKAGE LOOKUP (OFFICIAL AMAPI DIRECT) -->
    <!-- ========================================================================= -->
    <div class="tab-pane fade {{ !empty($keyword) ? 'show active' : '' }}" id="search-tab-pane" role="tabpanel">
        <!-- Search Input Card -->
        <div class="card card-mdm border-0 p-4 mb-4">
            <form method="GET" action="{{ route('apps.search') }}" class="row g-2">
                <div class="col-12 col-md-10">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-google-play fs-4 text-teal"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" 
                               placeholder="Ketik kata kunci aplikasi atau package name (contoh: WhatsApp, com.whatsapp, Zoom, Chrome)..." 
                               value="{{ $keyword }}" autofocus required>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                </div>
            </form>

            <!-- Quick Suggestions Chips -->
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                <span class="text-secondary small fw-medium me-1">Aplikasi Populer:</span>
                @php
                    $popularApps = [
                        ['name' => 'WhatsApp', 'pkg' => 'com.whatsapp'],
                        ['name' => 'Google Chrome', 'pkg' => 'com.android.chrome'],
                        ['name' => 'YouTube', 'pkg' => 'com.google.android.youtube'],
                        ['name' => 'Traccar GPS', 'pkg' => 'org.traccar.client'],
                        ['name' => 'Zoom Workplace', 'pkg' => 'us.zoom.videomeetings'],
                        ['name' => 'Microsoft Teams', 'pkg' => 'com.microsoft.teams'],
                        ['name' => 'TikTok', 'pkg' => 'com.zhiliaoapp.musically'],
                    ];
                @endphp
                @foreach($popularApps as $pApp)
                    <a href="{{ route('apps.search', ['q' => $pApp['pkg']]) }}" class="badge bg-light text-dark border text-decoration-none px-2 py-1">
                        <i class="bi bi-arrow-up-right me-1 text-teal"></i> {{ $pApp['name'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Search Results Grid -->
        @if(!empty($keyword))
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold text-dark mb-0">
                Hasil Pencarian: <span class="text-teal font-mono">"{{ $keyword }}"</span>
            </h6>
            <span class="text-secondary small">{{ count($results) }} aplikasi ditemukan</span>
        </div>

        <div class="row g-3 mb-4">
            @forelse($results as $index => $app)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card card-mdm border-0 h-100 p-3 d-flex flex-column justify-content-between shadow-sm">
                    <div>
                        <div class="d-flex gap-3 mb-3">
                            @if(!empty($app['icon']))
                                <img src="{{ $app['icon'] }}" alt="{{ $app['app_name'] }}" class="rounded-3 shadow-sm flex-shrink-0" style="width: 54px; height: 54px; object-fit: cover;">
                            @else
                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-teal border flex-shrink-0" style="width: 54px; height: 54px;">
                                    <i class="bi bi-google-play fs-3"></i>
                                </div>
                            @endif
                            <div class="overflow-hidden">
                                <div class="d-flex align-items-center gap-1">
                                    <h6 class="fw-bold text-dark text-truncate mb-0" title="{{ $app['app_name'] }}">{{ $app['app_name'] }}</h6>
                                </div>
                                <span class="text-secondary small d-block text-truncate mb-1">{{ $app['developer'] ?? 'Google Play Store' }}</span>
                                <span class="package-name text-muted small d-block text-truncate font-mono" style="font-size: 11px;">{{ $app['package_name'] }}</span>
                            </div>
                        </div>

                        @if(!empty($app['description']))
                            <p class="text-muted small text-truncate-2 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 12px;">
                                {{ $app['description'] }}
                            </p>
                        @endif
                    </div>

                    <form action="{{ route('apps.store') }}" method="POST" class="border-top pt-3 mt-auto">
                        @csrf
                        <input type="hidden" name="package_name" value="{{ $app['package_name'] }}">
                        <input type="hidden" name="app_name" value="{{ $app['app_name'] }}">
                        <input type="hidden" name="icon_url" value="{{ $app['icon'] ?? '' }}">
                        <input type="hidden" name="developer" value="{{ $app['developer'] ?? '' }}">

                        <div class="row g-2 align-items-center">
                            <div class="col-7">
                                <select name="policy_id" class="form-select form-select-sm" style="font-size: 12px;">
                                    <option value="">Hanya Simpan Katalog</option>
                                    @foreach($policies as $pol)
                                    <option value="{{ $pol->policy_id }}">+ {{ $pol->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-5">
                                <button type="submit" class="btn btn-sm btn-teal w-100 d-flex align-items-center justify-content-center gap-1 fw-medium">
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
    </div>
</div>

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
                        <div class="text-secondary small">Mengambil informasi resmi dari Google AMAPI...</div>
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
