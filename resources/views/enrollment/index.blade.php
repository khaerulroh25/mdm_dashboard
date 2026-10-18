@extends('layouts.app')

@section('title', 'Pendaftaran Perangkat Baru (Enrollment)')
@section('page_title', 'Pendaftaran Perangkat (Enrollment)')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-xl-6">
        <div class="card card-mdm border-0">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">Buat QR Code Pendaftaran Perangkat</h5>
                <span class="text-secondary small">Generate enrollment token Google Cloud untuk registrasi HP baru / reset</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('enrollment.generate') }}" method="POST">
                    @csrf

                    <!-- Policy Selection -->
                    <div class="mb-4">
                        <label for="policy_id" class="form-label small fw-semibold text-secondary">Pilih Kebijakan (Policy) Awal</label>
                        <select name="policy_id" id="policy_id" class="form-select form-select-lg" required>
                            @foreach($policies as $policy)
                            <option value="{{ $policy->policy_id }}" {{ $policy->policy_id == 'default' ? 'selected' : '' }}>
                                {{ $policy->name }} ({{ $policy->policy_id }})
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Perangkat akan langsung menerapkan seluruh aturan dan aplikasi yang terdaftar di policy ini begitu selesai scan QR.</div>
                    </div>

                    <!-- Information Alert -->
                    <div class="alert alert-info border-0 rounded-3 p-3 mb-4 d-flex gap-3" style="background-color: #F0FDF4; border-left: 4px solid #10B981 !important; color: #166534;">
                        <i class="bi bi-info-circle-fill fs-5 text-success flex-shrink-0"></i>
                        <div class="small">
                            <strong>Masa Berlaku Token:</strong> Token pendaftaran yang dihasilkan akan aktif selama <strong>7 hari</strong> secara default dan dapat digunakan untuk mendaftarkan banyak perangkat sekaligus.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-qr-code fs-5"></i>
                        <span>Generate Enrollment QR Code</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
