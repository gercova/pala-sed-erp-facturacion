<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso — EasyStock</title>
    <meta name="description" content="Inicia sesión o regístrate como cliente para gestionar tus pedidos de agua purificada.">

    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/img/favicon-white.ico') }}" type="image/x-icon">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* ── Wrapper ─────────────────────────────────── */
        .auth-wrapper {
            display: flex;
            width: 920px;
            max-width: 100%;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.04);
            overflow: hidden;
        }

        /* ── Form side ───────────────────────────────── */
        .auth-form-side {
            flex: 1;
            padding: 2.5rem 3rem;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            max-height: 90vh;
        }

        .brand-logo {
            width: 44px; height: 44px;
            background: #f0f4ff;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            border: 1px solid #dce8ff;
        }
        .brand-logo img { width: 28px; height: 28px; object-fit: contain; }

        /* ── Tabs ────────────────────────────────────── */
        .auth-tabs {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 1.75rem;
            gap: 0;
        }
        .auth-tab {
            flex: 1;
            padding: .65rem 1rem;
            font-size: .9rem;
            font-weight: 600;
            color: #6c757d;
            text-align: center;
            cursor: pointer;
            border: none;
            background: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
            letter-spacing: .01em;
        }
        .auth-tab.active {
            color: #0b5ed7;
            border-bottom-color: #0b5ed7;
        }
        .auth-tab:hover:not(.active) {
            color: #343a40;
        }

        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── Inputs ──────────────────────────────────── */
        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: .35rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .form-control, .form-select {
            width: 100%;
            border-radius: 10px;
            padding: .7rem 1rem;
            border: 1.5px solid #dee2e6;
            background-color: #f8f9fa;
            font-size: .9rem;
            font-family: inherit;
            transition: all .2s;
            outline: none;
            color: #212529;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: #0b5ed7;
            box-shadow: 0 0 0 3px rgba(11, 94, 215, .12);
        }
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, .1);
        }
        .invalid-feedback {
            display: block;
            font-size: .8rem;
            color: #dc3545;
            margin-top: .3rem;
        }
        .input-group { position: relative; }
        .input-group .form-control { padding-right: 3rem; }
        .toggle-pass {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #6c757d; padding: 0; line-height: 1;
        }

        /* ── Btn ─────────────────────────────────────── */
        .btn-auth {
            width: 100%;
            padding: .8rem;
            background: #0b5ed7;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .25s;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            font-family: inherit;
        }
        .btn-auth:hover {
            background: #0a53be;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(11, 94, 215, .28);
        }
        .btn-auth:active { transform: translateY(0); }

        /* ── Alert ───────────────────────────────────── */
        .auth-alert {
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .875rem;
            margin-bottom: 1rem;
            display: flex; align-items: flex-start; gap: .6rem;
        }
        .auth-alert.danger { background: #fff5f5; border: 1px solid #fecaca; color: #dc2626; }
        .auth-alert.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

        /* ── Form rows ───────────────────────────────── */
        .form-row { display: grid; gap: .75rem; margin-bottom: .75rem; }
        .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
        .form-group { margin-bottom: .75rem; }

        .form-divider {
            display: flex; align-items: center; gap: .75rem;
            margin: 1.25rem 0;
            color: #adb5bd; font-size: .8rem;
        }
        .form-divider::before, .form-divider::after {
            content: ''; flex: 1; height: 1px; background: #e9ecef;
        }

        /* ── Info side ───────────────────────────────── */
        .auth-info-side {
            width: 340px;
            flex-shrink: 0;
            background: #f8f9fb;
            border-left: 1px solid #e9ecef;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .info-title { font-size: 1.35rem; font-weight: 700; color: #212529; margin-bottom: .5rem; }
        .info-subtitle { font-size: .875rem; color: #6c757d; margin-bottom: 2rem; line-height: 1.6; }

        .feature-item { display: flex; align-items: flex-start; gap: .875rem; margin-bottom: 1.5rem; }
        .feature-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: #fff; border: 1px solid #e9ecef;
            display: flex; align-items: center; justify-content: center;
            color: #0b5ed7; flex-shrink: 0;
            font-size: .95rem;
        }
        .feature-text h6 { font-size: .9rem; font-weight: 600; color: #212529; margin: 0 0 .2rem; }
        .feature-text p  { font-size: .8rem; color: #6c757d; margin: 0; line-height: 1.5; }

        .zone-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: #e8f4fd; border: 1px solid #bee3f8;
            color: #1a6fa8; font-size: .75rem; font-weight: 600;
            padding: .3rem .65rem; border-radius: 20px; margin: .2rem;
        }

        /* ── Footer ──────────────────────────────────── */
        .auth-footer {
            margin-top: 1.5rem;
            font-size: .78rem;
            color: #adb5bd;
            text-align: center;
        }

        /* ── Password strength ───────────────────────── */
        .pass-strength { margin-top: .4rem; }
        .pass-strength-bar {
            height: 4px; border-radius: 4px;
            background: #e9ecef; overflow: hidden;
        }
        .pass-strength-fill {
            height: 100%; border-radius: 4px;
            width: 0; transition: width .3s, background .3s;
        }
        .pass-strength-label { font-size: .75rem; margin-top: .25rem; color: #6c757d; }

        @media (max-width: 768px) {
            body { padding: 1rem; align-items: flex-start; }
            .auth-info-side { display: none; }
            .auth-wrapper { max-width: 460px; }
            .auth-form-side { padding: 2rem 1.75rem; max-height: none; }
            .form-row.cols-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <!-- ── Form Side ─────────────────────────────────────────────────── -->
    <div class="auth-form-side">

        <!-- Logo -->
        <div class="brand-logo">
            @if(empty($logo))
                <img src="{{ asset('files/empty_logo.png') }}" alt="Logo">
            @else
                <img src="{{ asset('files/logos/' . $logo) }}" alt="Logo">
            @endif
        </div>

        <!-- Global alert -->
        @if(session('message'))
            <div class="auth-alert danger" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        <!-- ── Tabs ───────────────────────────────────────────────────── -->
        <div class="auth-tabs" role="tablist">
            <button class="auth-tab {{ old('active_tab', $errors->has('reg_') || session('active_tab') === 'register' ? 'register' : 'login') !== 'register' ? 'active' : '' }}"
                    id="tab-login" role="tab" aria-controls="pane-login" aria-selected="true"
                    onclick="switchTab('login')">
                <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
            </button>
            <button class="auth-tab {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_iddoc','reg_nro_doc','reg_nombres','reg_telefono','reg_ubigeo','reg_direccion','reg_password']) ? 'active' : '' }}"
                    id="tab-register" role="tab" aria-controls="pane-register" aria-selected="false"
                    onclick="switchTab('register')">
                <i class="fas fa-user-plus me-1"></i> Registrarse
            </button>
        </div>

        <!-- ══ TAB: LOGIN ════════════════════════════════════════════════ -->
        <div class="tab-pane {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_iddoc','reg_nro_doc','reg_nombres','reg_telefono','reg_ubigeo','reg_direccion','reg_password']) ? '' : 'active' }}"
             id="pane-login" role="tabpanel">

            <h2 style="font-size:1.4rem; font-weight:700; color:#212529; margin:0 0 .3rem;">Bienvenido</h2>
            <p style="font-size:.875rem; color:#6c757d; margin:0 0 1.5rem;">Ingresa tus credenciales para continuar.</p>

            <form method="POST" action="{{ route('login.login') }}" novalidate id="form-login">
                @csrf
                <div class="form-group">
                    <label for="user" class="form-label">Usuario</label>
                    <input id="user" type="text" name="user" autocomplete="username" autofocus
                           class="form-control" placeholder="Tu nombre de usuario"
                           value="{{ old('user') }}">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input id="password" type="password" name="password" autocomplete="current-password"
                               class="form-control" placeholder="••••••••">
                        <button type="button" class="toggle-pass" onclick="togglePass('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-auth" id="btn-login" style="margin-top:1rem;">
                    <span>Acceder</span>
                    <i class="fas fa-arrow-right" style="font-size:.85rem;"></i>
                </button>
            </form>

            <div class="form-divider">o</div>
            <p style="text-align:center; font-size:.875rem; color:#6c757d; margin:0;">
                ¿Eres cliente nuevo?
                <a href="#" onclick="switchTab('register'); return false;" style="color:#0b5ed7; font-weight:600; text-decoration:none;">
                    Regístrate gratis
                </a>
            </p>
        </div>

        <!-- ══ TAB: REGISTRO ════════════════════════════════════════════ -->
        <div class="tab-pane {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_iddoc','reg_nro_doc','reg_nombres','reg_telefono','reg_ubigeo','reg_direccion','reg_password']) ? 'active' : '' }}"
             id="pane-register" role="tabpanel">

            <h2 style="font-size:1.4rem; font-weight:700; color:#212529; margin:0 0 .3rem;">Crear cuenta</h2>
            <p style="font-size:.875rem; color:#6c757d; margin:0 0 1.5rem;">Regístrate para hacer pedidos en línea y acumular promociones.</p>

            <form method="POST" action="{{ route('login.register') }}" novalidate id="form-register">
                @csrf

                <!-- Tipo doc + Nro doc -->
                <div class="form-row cols-2">
                    <div>
                        <label for="reg_iddoc" class="form-label">Tipo de Doc.</label>
                        <select id="reg_iddoc" name="reg_iddoc"
                                class="form-select {{ $errors->has('reg_iddoc') ? 'is-invalid' : '' }}">
                            <option value="">— Seleccionar —</option>
                            @foreach($docTypes ?? [] as $dt)
                                <option value="{{ $dt->id }}" {{ old('reg_iddoc') == $dt->id ? 'selected' : '' }}>
                                    {{ $dt->codigo }} — {{ $dt->descripcion_documento }}
                                </option>
                            @endforeach
                        </select>
                        @error('reg_iddoc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="reg_nro_doc" class="form-label">Nro. Documento</label>
                        <input id="reg_nro_doc" type="text" name="reg_nro_doc"
                               class="form-control {{ $errors->has('reg_nro_doc') ? 'is-invalid' : '' }}"
                               placeholder="Ej. 42156789"
                               value="{{ old('reg_nro_doc') }}" maxlength="20">
                        @error('reg_nro_doc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Nombre -->
                <div class="form-group">
                    <label for="reg_nombres" class="form-label">Nombre completo / Razón Social</label>
                    <input id="reg_nombres" type="text" name="reg_nombres"
                           class="form-control {{ $errors->has('reg_nombres') ? 'is-invalid' : '' }}"
                           placeholder="Juan Pérez García"
                           value="{{ old('reg_nombres') }}" maxlength="255">
                    @error('reg_nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Teléfono -->
                <div class="form-group">
                    <label for="reg_telefono" class="form-label">Teléfono / Celular</label>
                    <input id="reg_telefono" type="tel" name="reg_telefono"
                           class="form-control {{ $errors->has('reg_telefono') ? 'is-invalid' : '' }}"
                           placeholder="942 123 456"
                           value="{{ old('reg_telefono') }}" maxlength="15">
                    @error('reg_telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Ubigeo + Dirección -->
                <div class="form-row cols-2">
                    <div>
                        <label for="reg_ubigeo" class="form-label">Distrito</label>
                        <select id="reg_ubigeo" name="reg_ubigeo"
                                class="form-select {{ $errors->has('reg_ubigeo') ? 'is-invalid' : '' }}">
                            <option value="">— Seleccionar —</option>
                            <option value="220601" {{ old('reg_ubigeo') === '220601' ? 'selected' : '' }}>Tarapoto</option>
                            <option value="220602" {{ old('reg_ubigeo') === '220602' ? 'selected' : '' }}>Morales</option>
                            <option value="220603" {{ old('reg_ubigeo') === '220603' ? 'selected' : '' }}>La Banda de Shilcayo</option>
                            <option value="220609" {{ old('reg_ubigeo') === '220609' ? 'selected' : '' }}>Shapaja</option>
                        </select>
                        @error('reg_ubigeo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="reg_direccion" class="form-label">Dirección</label>
                        <input id="reg_direccion" type="text" name="reg_direccion"
                               class="form-control {{ $errors->has('reg_direccion') ? 'is-invalid' : '' }}"
                               placeholder="Jr. Lima 123"
                               value="{{ old('reg_direccion') }}" maxlength="255">
                        @error('reg_direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="form-row cols-2">
                    <div>
                        <label for="reg_password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input id="reg_password" type="password" name="reg_password"
                                   class="form-control {{ $errors->has('reg_password') ? 'is-invalid' : '' }}"
                                   placeholder="Mín. 8 caracteres" minlength="8"
                                   oninput="updateStrength(this.value)">
                            <button type="button" class="toggle-pass" onclick="togglePass('reg_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="pass-strength">
                            <div class="pass-strength-bar">
                                <div class="pass-strength-fill" id="strength-fill"></div>
                            </div>
                            <div class="pass-strength-label" id="strength-label"></div>
                        </div>
                        @error('reg_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="reg_password_confirmation" class="form-label">Confirmar</label>
                        <div class="input-group">
                            <input id="reg_password_confirmation" type="password" name="reg_password_confirmation"
                                   class="form-control" placeholder="Repite la contraseña" minlength="8">
                            <button type="button" class="toggle-pass" onclick="togglePass('reg_password_confirmation', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-auth" id="btn-register" style="margin-top:.75rem;">
                    <i class="fas fa-user-plus" style="font-size:.85rem;"></i>
                    <span>Crear mi cuenta</span>
                </button>
            </form>

            <div class="form-divider">o</div>
            <p style="text-align:center; font-size:.875rem; color:#6c757d; margin:0;">
                ¿Ya tienes cuenta?
                <a href="#" onclick="switchTab('login'); return false;" style="color:#0b5ed7; font-weight:600; text-decoration:none;">
                    Iniciar sesión
                </a>
            </p>
        </div>

        <div class="auth-footer">&copy; {{ date('Y') }} EasyStock POS.</div>
    </div>

    <!-- ── Info Side ─────────────────────────────────────────────────── -->
    <div class="auth-info-side">
        <p class="info-title">¿Cómo funciona?</p>
        <p class="info-subtitle">Regístrate y realiza tus pedidos de agua purificada directamente desde aquí.</p>

        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-user-circle"></i></div>
            <div class="feature-text">
                <h6>Crea tu cuenta gratis</h6>
                <p>Solo necesitas tu DNI o RUC y un teléfono de contacto.</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-box-open"></i></div>
            <div class="feature-text">
                <h6>Haz tu pedido</h6>
                <p>Selecciona productos, fecha y método de pago (Efectivo, Yape, Plin).</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-star"></i></div>
            <div class="feature-text">
                <h6>Gana promociones</h6>
                <p>Por cada 5 pedidos de recarga, <strong>¡el 6.º es gratis!</strong></p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="feature-text">
                <h6>Zona de cobertura</h6>
                <p style="margin-bottom:.5rem;">Atendemos en:</p>
                <div>
                    <span class="zone-badge"><i class="fas fa-check-circle"></i> Tarapoto</span>
                    <span class="zone-badge"><i class="fas fa-check-circle"></i> Morales</span>
                    <span class="zone-badge"><i class="fas fa-check-circle"></i> Banda de Shilcayo</span>
                    <span class="zone-badge"><i class="fas fa-check-circle"></i> Shapaja</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('ajax/libs/font-awesome/6.3.0/js/all.min.js') }}" defer></script>
<script src="{{ asset('npm/bootstrap%405.2.3/dist/js/bootstrap.bundle.min.js') }}"></script>
<script>
(function () {
    'use strict';

    // Tab switching
    window.switchTab = function (tab) {
        document.querySelectorAll('.auth-tab').forEach(t => {
            t.classList.toggle('active', t.id === 'tab-' + tab);
            t.setAttribute('aria-selected', t.id === 'tab-' + tab);
        });
        document.querySelectorAll('.tab-pane').forEach(p => {
            p.classList.toggle('active', p.id === 'pane-' + tab);
        });
    };

    // Toggle password visibility
    window.togglePass = function (inputId, btn) {
        const input = document.getElementById(inputId);
        const icon  = btn.querySelector('i');
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
    };

    // Password strength meter
    window.updateStrength = function (val) {
        const fill  = document.getElementById('strength-fill');
        const label = document.getElementById('strength-label');
        if (!fill || !label) return;

        let score = 0;
        if (val.length >= 8)               score++;
        if (/[A-Z]/.test(val))             score++;
        if (/[0-9]/.test(val))             score++;
        if (/[^A-Za-z0-9]/.test(val))      score++;

        const levels = [
            { w: '0%',   bg: '#e9ecef', txt: '' },
            { w: '25%',  bg: '#dc3545', txt: 'Débil' },
            { w: '50%',  bg: '#fd7e14', txt: 'Regular' },
            { w: '75%',  bg: '#ffc107', txt: 'Buena' },
            { w: '100%', bg: '#198754', txt: '¡Segura!' },
        ];
        const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 4)];
        fill.style.width      = lvl.w;
        fill.style.background = lvl.bg;
        label.textContent     = lvl.txt;
        label.style.color     = lvl.bg;
    };

    // Loading state on submit
    document.getElementById('form-login')?.addEventListener('submit', function () {
        const btn = document.getElementById('btn-login');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verificando...</span>';
        btn.disabled = true;
    });

    document.getElementById('form-register')?.addEventListener('submit', function () {
        const btn = document.getElementById('btn-register');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Registrando...</span>';
        btn.disabled = true;
    });
})();
</script>
</body>
</html>