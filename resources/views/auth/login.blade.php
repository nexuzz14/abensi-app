<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Oobake Bakery</title>
    <meta name="description" content="Login ke sistem absensi karyawan Oobake Bakery">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
            /* Warm bakery gradient — deep espresso to chestnut */
            background: #2c1503;
        }

        /* Warm ambient glow — like light from an oven */
        body::before {
            content: '';
            position: absolute;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(184,116,42,0.18) 0%, transparent 65%);
            top: -250px; right: -200px;
            border-radius: 50%;
            animation: warm-pulse 5s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 550px; height: 550px;
            background: radial-gradient(circle, rgba(124,74,30,0.2) 0%, transparent 65%);
            bottom: -200px; left: -150px;
            border-radius: 50%;
            animation: warm-pulse 5s ease-in-out infinite reverse;
        }

        @keyframes warm-pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50%       { transform: scale(1.08); opacity: 1; }
        }

        /* Floating warm blobs */
        .blob {
            position: absolute; border-radius: 50%;
            background: rgba(232,168,74,0.04);
            animation: float 7s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50%       { transform: translateY(-22px) rotate(5deg); }
        }

        /* ========================
           LOGIN CARD
        ======================== */
        .login-wrapper {
            position: relative; z-index: 10;
            width: 100%; max-width: 420px; padding: 1rem;
        }

        .login-card {
            background: rgba(253,248,242,0.04);
            border: 1px solid rgba(232,168,74,0.18);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(24px);
            box-shadow:
                0 30px 60px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.06);
        }

        /* Logo — circular bread mascot vibes */
        .login-logo {
            width: 72px; height: 72px;
            background: #fdf8f2;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 0.875rem;
            box-shadow: 0 8px 28px rgba(184,116,42,0.35);
            animation: logo-warm 3s ease-in-out infinite;
        }

        @keyframes logo-warm {
            0%, 100% { box-shadow: 0 8px 28px rgba(184,116,42,0.35); }
            50%       { box-shadow: 0 10px 36px rgba(184,116,42,0.55), 0 0 50px rgba(232,168,74,0.15); }
        }

        .login-brand {
            text-align: center;
            color: #fdf8f2;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
        }

        .login-subtitle {
            color: rgba(212,184,150,0.65);
            font-size: 0.78rem;
            text-align: center;
            margin-bottom: 1.875rem;
        }

        /* ========================
           FORM INPUTS
        ======================== */
        .form-group-custom { margin-bottom: 1rem; }

        .form-label-custom {
            color: rgba(212,184,150,0.75);
            font-size: 0.72rem; font-weight: 700;
            margin-bottom: 0.4rem; display: block;
            text-transform: uppercase; letter-spacing: 0.08em;
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute; left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: rgba(184,116,42,0.5);
            font-size: 0.9rem; z-index: 1;
        }

        .form-control-custom {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(232,168,74,0.2);
            border-radius: 12px;
            color: #fdf8f2;
            font-size: 0.9rem;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            transition: all 0.2s; outline: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .form-control-custom::placeholder { color: rgba(255,255,255,0.18); }

        .form-control-custom:focus {
            background: rgba(255,255,255,0.08);
            border-color: rgba(184,116,42,0.55);
            box-shadow: 0 0 0 3px rgba(184,116,42,0.12);
        }

        .form-control-custom.is-invalid { border-color: rgba(185,50,50,0.6); }

        .invalid-feedback-custom { color: #fca5a5; font-size: 0.73rem; margin-top: 0.35rem; }

        .toggle-password {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%);
            color: rgba(184,116,42,0.4);
            cursor: pointer; background: none; border: none;
            padding: 0; font-size: 0.9rem; transition: color 0.2s;
        }

        .toggle-password:hover { color: rgba(184,116,42,0.8); }

        /* ========================
           REMEMBER ME
        ======================== */
        .remember-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
        .form-check-custom input { accent-color: #b8742a; }
        .form-check-custom label { color: rgba(212,184,150,0.6); font-size: 0.8rem; cursor: pointer; }

        /* ========================
           SUBMIT BUTTON
        ======================== */
        .btn-login {
            width: 100%;
            background: #b8742a;
            border: none; border-radius: 12px;
            color: white; font-weight: 700;
            font-size: 0.95rem; padding: 0.875rem;
            cursor: pointer; transition: all 0.25s;
            position: relative; overflow: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .btn-login::before {
            content: ''; position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.55s;
        }

        .btn-login:hover::before { left: 100%; }
        .btn-login:hover { background: #d4922f; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(184,116,42,0.45); }
        .btn-login:active { transform: translateY(0); }

        /* ========================
           INFO BLOCK
        ======================== */
        .login-divider { border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 1.5rem 0; }

        .login-info {
            background: rgba(184,116,42,0.08);
            border: 1px solid rgba(184,116,42,0.2);
            border-radius: 10px; padding: 0.875rem 1rem;
        }

        .login-info-title {
            color: #d4b896; font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem;
        }

        .login-info-row {
            color: rgba(212,184,150,0.65); font-size: 0.75rem;
            display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;
        }

        .login-info-row:last-child { margin-bottom: 0; }
        .login-info-row strong { color: rgba(253,248,242,0.85); }

        /* Error alert */
        .alert-login {
            background: rgba(185,50,50,0.12);
            border: 1px solid rgba(185,50,50,0.3);
            border-radius: 10px; color: #fca5a5;
            padding: 0.625rem 0.875rem; font-size: 0.8rem;
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Decorative blobs -->
    <div class="blob" style="width:100px;height:100px;top:8%;left:6%;animation-delay:0s"></div>
    <div class="blob" style="width:60px;height:60px;top:55%;right:7%;animation-delay:2.5s"></div>
    <div class="blob" style="width:40px;height:40px;top:28%;right:18%;animation-delay:1.2s"></div>
    <div class="blob" style="width:140px;height:140px;bottom:6%;left:12%;animation-delay:3.5s"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Logo / Mascot -->
            <div class="login-logo">🍞</div>

            <h1 class="login-brand">Oobake Bakery</h1>
            <p class="login-subtitle">Sistem Absensi Karyawan</p>

            <!-- Error Alert -->
            @if($errors->has('identifier'))
                <div class="alert-login">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $errors->first('identifier') }}
                </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                @csrf

                <!-- NIP / Email -->
                <div class="form-group-custom">
                    <label class="form-label-custom" for="identifier">NIP / Email Admin</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-badge-fill input-icon"></i>
                        <input type="text" id="identifier" name="identifier"
                               class="form-control-custom {{ $errors->has('identifier') ? 'is-invalid' : '' }}"
                               placeholder="Masukkan NIP atau Email Admin"
                               value="{{ old('identifier') }}"
                               autocomplete="username" required>
                    </div>
                    @error('identifier')
                        <div class="invalid-feedback-custom">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group-custom">
                    <label class="form-label-custom" for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" id="password" name="password"
                               class="form-control-custom {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Masukkan password Anda"
                               autocomplete="current-password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback-custom">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="remember-row" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="form-check form-check-custom mb-0">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input">
                        <label for="remember" class="form-check-label ms-1">Ingat saya</label>
                    </div>
                    <div id="forgotAdminWrapper" style="display: none;">
                        <a href="#" onclick="alert('Silakan hubungi tim IT / Developer untuk melakukan reset kata sandi Admin.'); return false;" style="color: #d4922f; font-size: 0.8rem; text-decoration: none; font-weight: 600;">Lupa Sandi Admin?</a>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="submitBtn">
                    <span id="btnText">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Sistem
                    </span>
                    <span id="btnLoading" style="display:none">
                        <span class="spinner-border spinner-border-sm me-2"></span> Sedang masuk...
                    </span>
                </button>
            </form>

            <!-- Info Demo -->
            <hr class="login-divider">
            <div class="login-info">
                <div class="login-info-title"><i class="bi bi-info-circle me-1"></i> Akun Demo</div>
                <div class="login-info-row">
                    <i class="bi bi-shield-check" style="color:#d4b896"></i>
                    Admin: <strong>admin@sistem.com</strong> / <strong>Admin123!</strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle visibility password
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggleIcon');
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
        }

        // Tampilkan link Lupa Sandi Admin HANYA jika mengetik Email atau 'admin'
        document.getElementById('identifier').addEventListener('input', function() {
            const val = this.value.toLowerCase();
            const wrapper = document.getElementById('forgotAdminWrapper');
            if (val.includes('@') || val === 'admin') {
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
            }
        });

        // Loading state saat form disubmit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn     = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoad = document.getElementById('btnLoading');
            btn.disabled  = true;
            btnText.style.display = 'none';
            btnLoad.style.display = 'inline';
        });
    </script>
</body>
</html>
