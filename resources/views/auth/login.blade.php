<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Amanin - Device Management Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme-tosca.css') }}">

    <style>
        body {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.25);
            padding: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Logo & Title -->
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo-amanin-transparent.png') }}" alt="Amanin - Device Management Platform" class="img-fluid mb-2" style="max-height: 64px; object-fit: contain;">
            <p class="text-secondary small mb-0">Android Enterprise Management Portal</p>
        </div>

        <!-- Flash Alerts -->
        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 small py-2 px-3 border-0 rounded-3 mb-3" style="background-color: #FFE4E6; color: #9F1239;">
            <i class="bi bi-exclamation-circle-fill fs-6 text-danger"></i>
            <div>{{ session('error') }}</div>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 small py-2 px-3 border-0 rounded-3 mb-3" style="background-color: #ECFDF5; color: #065F46;">
            <i class="bi bi-check-circle-fill fs-6 text-success"></i>
            <div>{!! session('success') !!}</div>
        </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('login.action') }}">
            @csrf

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0 @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required autofocus>
                </div>
                @error('username')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required>
                </div>
                @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-secondary" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-in-right fs-5"></i>
                <span>Masuk ke Dashboard</span>
            </button>
        </form>

    </div>
</body>
</html>
