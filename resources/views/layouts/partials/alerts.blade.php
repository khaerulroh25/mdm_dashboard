@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert" style="background-color: #ECFDF5; color: #065F46; border-left: 4px solid #10B981 !important;">
    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
    <div class="flex-grow-1">{!! session('success') !!}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert" style="background-color: #FFE4E6; color: #9F1239; border-left: 4px solid #E11D48 !important;">
    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
    <div class="flex-grow-1">{!! session('error') !!}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(isset($errors) && $errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background-color: #FFE4E6; color: #9F1239; border-left: 4px solid #E11D48 !important;">
    <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
        <strong class="fw-bold">Terdapat beberapa kesalahan input:</strong>
    </div>
    <ul class="mb-0 ps-4 small">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
