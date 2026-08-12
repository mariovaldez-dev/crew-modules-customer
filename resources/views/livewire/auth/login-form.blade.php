<div class="fixed inset-0 overflow-y-auto bg-[#f4f7f5] dark:bg-[#0B0F12] z-[60]" x-data="{ hasError: @entangle('hasError') }">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --primary: #1f8f3a;
            --primary-dark: #166a2b;
            --primary-soft: #eaf7ee;
            --text: #1b1b1b;
            --muted: #6b7280;
            --border: #d9e1e7;
            --white: #ffffff;
            --shadow: 0 20px 50px rgba(0, 0, 0, .12);
            --radius-xl: 24px;
        }

        .login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.2fr .9fr;
        }

        .login-left{
            position:relative;
            overflow:hidden;
            background-color: #082312; /* Fallback de color sólido */
            background-image: url("{{ asset('imp-atardecer.png') }}"); /* Fallback de imagen PNG */
            background-image: url("{{ asset('imp-atardecer.webp') }}"); /* Imagen principal WebP */
            background-size:cover;
            background-position:center center;
            background-repeat:no-repeat;
        }

        .login-left::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(8, 35, 18, .52), rgba(17, 79, 40, .20)),
                linear-gradient(to top, rgba(0, 0, 0, .26), rgba(0, 0, 0, .08));
        }

        .login-left-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }

        .brand-box {
            max-width: 640px;
            color: #fff;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .18);
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .brand-title {
            font-size: 54px;
            line-height: 1.05;
            font-weight: 800;
            margin-bottom: 18px;
            text-shadow: 0 4px 18px rgba(0, 0, 0, .18);
        }

        .brand-title span {
            color: #d0ffd8;
        }

        .brand-text {
            font-size: 18px;
            line-height: 1.8;
            color: rgba(255, 255, 255, .95);
            max-width: 560px;
            text-shadow: 0 2px 12px rgba(0, 0, 0, .18);
        }

        .brand-footer {
            margin-top: 30px;
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .brand-chip {
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .15);
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 14px;
            color: rgba(255, 255, 255, .94);
        }

        .login-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 28px;
            background:
                radial-gradient(circle at top right, #eefaf1, transparent 30%),
                #f6f8f7;
        }

        .login-card {
            width: 100%;
            max-width: 470px;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(255, 255, 255, .9);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            padding: 34px 32px 28px;
        }

        .login-logo-img {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }

        .login-logo-img img {
            width: 95px;
            height: 95px;
            object-fit: contain;
        }

        .login-title {
            font-size: 36px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 8px;
            color: var(--text);
        }

        .login-subtitle {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 28px;
            text-align: center;
            max-width: 340px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #25313c;
            margin-bottom: 9px;
            text-transform: none;
            letter-spacing: normal;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #8a97a5;
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            height: 56px;
            border: 1.8px solid var(--border);
            border-radius: 14px;
            padding: 0 16px 0 46px;
            font-size: 15px;
            background: #fff;
            transition: .25s;
            outline: none;
            color: var(--text);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(31, 143, 58, .10);
        }

        .password-wrap .form-control {
            padding-right: 52px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4b5563;
            cursor: pointer;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            height: 56px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(31, 143, 58, .22);
            transition: all .25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(31, 143, 58, .28);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
        }

        .alert-custom {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #fff4f4;
            border: 1px solid #f1c1c1;
            color: #9d2222;
            font-size: 14px;
            text-align: left;
        }

        .alert-custom strong {
            display: block;
            margin-bottom: 4px;
        }

        .alert-custom ul {
            margin: 4px 0 0 18px;
        }

        .support-fab {
            position: fixed;
            right: 22px;
            bottom: 22px;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #f0dd2a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            box-shadow: 0 14px 30px rgba(0, 0, 0, .18);
            z-index: 50;
            transition: transform .25s ease;
        }

        .support-fab:hover {
            transform: scale(1.06);
        }

        .support-fab i {
            font-size: 22px;
        }

        @media(max-width:1100px) {
            .login-wrapper {
                grid-template-columns: 1fr 1fr;
            }

            .brand-title {
                font-size: 44px;
            }
        }

        @media(max-width:860px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-left {
                min-height: 320px;
                background-position: center center;
            }

            .login-left-content {
                padding: 40px 24px;
            }

            .brand-title {
                font-size: 38px;
            }

            .brand-text {
                font-size: 16px;
                line-height: 1.7;
            }

            .login-right {
                padding: 22px 16px 30px;
                margin-top: -34px;
                position: relative;
                z-index: 3;
            }

            .login-card {
                max-width: 100%;
                border-radius: 24px 24px 18px 18px;
                padding: 28px 22px 24px;
            }
        }

        @media(max-width:480px) {
            .brand-title {
                font-size: 32px;
            }

            .form-control,
            .btn-login {
                height: 52px;
            }
        }

        /* ==================== DARK MODE ==================== */
        .dark .login-right {
            background:
                radial-gradient(circle at top right, rgba(31, 143, 58, 0.12), transparent 35%),
                #0B0F12;
        }

        .dark .login-card {
            background: rgba(19, 27, 32, 0.95);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .dark .login-title {
            color: #ffffff;
        }

        .dark .login-subtitle {
            color: #9ca3af;
        }

        .dark .form-label {
            color: #d1d5db;
        }

        .dark .input-icon {
            color: #6b7280;
        }

        .dark .form-control {
            background: #18232a;
            border-color: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }

        .dark .form-control::placeholder {
            color: #6b7280;
        }

        .dark .form-control:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
            background: #18232a;
        }

        .dark .toggle-password {
            color: #9ca3af;
        }

        .dark .toggle-password:hover {
            color: #22c55e;
        }

        .dark .login-footer {
            color: #6b7280;
        }

        .dark .alert-custom {
            background: rgba(69, 10, 10, 0.4);
            border-color: rgba(127, 29, 29, 0.5);
            color: #f87171;
        }

        .theme-toggle-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 50;
            color: #4b5563;
            transition: all .2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .dark .theme-toggle-btn {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.12);
            color: #fbbf24;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .theme-toggle-btn:hover {
            transform: scale(1.06);
        }
    </style>

    <div class="login-wrapper">

        <div class="login-left">
            <div class="login-left-content">
                <div class="brand-box">

                    <div class="brand-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        Bienvenido a Grupo Impulsora
                    </div>

                    <h1 class="brand-title">
                        Impulsora Digital <span>- Maniobra</span>
                    </h1>

                    <p class="brand-text">
                        Accede al Módulo de Maniobra de Grupo Impulsora desde una
                        experiencia moderna, segura y alineada con el ecosistema de
                        Impulsora Digital.
                    </p>

                    <div class="brand-footer">
                        <div class="brand-chip">Seguridad</div>
                        <div class="brand-chip">Control</div>
                        <div class="brand-chip">Administración centralizada</div>
                    </div>

                </div>
            </div>
        </div>

        <div class="login-right relative" x-data="{ 
            isDark: document.documentElement.classList.contains('dark'),
            toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                    this.isDark = false;
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                    this.isDark = true;
                }
            }
        }">
            <!-- Toggle Theme Button -->
            <button @click="toggleTheme" type="button" class="theme-toggle-btn" title="Cambiar Tema">
                <i class="fa-solid" :class="isDark ? 'fa-sun text-amber-400 text-lg' : 'fa-moon text-gray-600 text-lg'"></i>
            </button>
            <div class="login-card">

                <div class="login-logo-img">
                    <img src="{{ asset('impulsora.png') }}" alt="Impulsora Digital">
                </div>

                <h2 class="login-title">Iniciar sesión</h2>

                <p class="login-subtitle">
                    Ingresa con tu cuenta para acceder al Módulo de Operaciones.
                </p>

                {{-- Mensajes de Error Custom --}}
                <div x-show="hasError || $wire.errors.length > 0" x-cloak style="display:none">
                    <div class="alert-custom">
                        <strong>Hay problemas con tu inicio de sesión.</strong>
                        <ul>
                            @if($errors->has('username'))
                            <li>{{ $errors->first('username') }}</li> @endif
                            @if($errors->has('password'))
                            <li>{{ $errors->first('password') }}</li> @endif
                            @if($errors->has('auth_error'))
                            <li>{{ $errors->first('auth_error') }}</li> @endif
                        </ul>
                    </div>
                </div>

                <form wire:submit.prevent="login">
                    <div class="form-group">
                        <label class="form-label">Usuario</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <i class="fa-regular fa-user"></i>
                            </span>

                            <input type="text" wire:model.live="username" @input="hasError = false" class="form-control"
                                placeholder="Tu identificador" required autofocus>
                        </div>
                    </div>

                    <div class="form-group" x-data="{ show: false }">
                        <label class="form-label">Contraseña</label>

                        <div class="input-wrap password-wrap">
                            <span class="input-icon">
                                <i class="fa-solid fa-lock"></i>
                            </span>

                            <input :type="show ? 'text' : 'password'" wire:model.live="password"
                                @input="hasError = false" class="form-control" placeholder="Ingresa tu contraseña"
                                required>

                            <span class="toggle-password" @click="show = !show">
                                <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="login" style="display:inline">Iniciar sesión</span>
                        <span wire:loading wire:target="login" class="inline-flex items-center gap-2" style="display:none">
                            <i class="fa-solid fa-circle-notch fa-spin"></i> Verificando...
                        </span>
                    </button>
                </form>

                <div class="login-footer">
                    © {{ date('Y') }} Impulsora Digital.
                </div>

            </div>
        </div>

    </div>

    <a href="https://sites.google.com/grupoimpulsora.com/centrodeayuda/p%C3%A1gina-principal" target="_blank"
        class="support-fab" title="Solicitud de soporte">
        <i class="fa-regular fa-lightbulb"></i>
    </a>
</div>