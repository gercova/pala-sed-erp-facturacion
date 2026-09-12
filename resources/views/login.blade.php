<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso — EasyStock</title>
    <meta name="description"
        content="Inicia sesión o regístrate como cliente para gestionar tus pedidos de agua purificada.">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/img/favicon-white.ico') }}" type="image/x-icon">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>
    <div class="auth-wrapper">
        <!-- ── Form Side ─────────────────────────────────────────────────── -->
        <div class="auth-form-side">

            <!-- Logo -->
            <div class="brand-logo">
                @if (empty($logo))
                    <img src="{{ asset('files/empty_logo.png') }}" alt="Logo">
                @else
                    <img src="{{ asset('files/logos/' . $logo) }}" alt="Logo">
                @endif
            </div>

            <!-- Global alert -->
            @if (session('message'))
                <div class="auth-alert danger" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <!-- ── Tabs ───────────────────────────────────────────────────── -->
            <div class="auth-tabs" role="tablist">
                <button
                    class="auth-tab {{ old('active_tab', $errors->has('reg_') || session('active_tab') === 'register' ? 'register' : 'login') !== 'register' ? 'active' : '' }}"
                    id="tab-login" role="tab" aria-controls="pane-login" aria-selected="true"
                    onclick="switchTab('login')">
                    <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                </button>
                <button
                    class="auth-tab {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_iddoc', 'reg_nro_doc', 'reg_nombres', 'reg_telefono', 'reg_ubigeo', 'reg_direccion', 'reg_password']) ? 'active' : '' }}"
                    id="tab-register" role="tab" aria-controls="pane-register" aria-selected="false"
                    onclick="switchTab('register')">
                    <i class="fas fa-user-plus me-1"></i> Registrarse
                </button>
            </div>

            <!-- ══ TAB: LOGIN ════════════════════════════════════════════════ -->
            <div class="tab-pane {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_iddoc', 'reg_nro_doc', 'reg_nombres', 'reg_telefono', 'reg_ubigeo', 'reg_direccion', 'reg_password']) ? '' : 'active' }}"
                id="pane-login" role="tabpanel">

                <h2 style="font-size:1.4rem; font-weight:700; color:#212529; margin:0 0 .3rem;">Bienvenido</h2>
                <p style="font-size:.875rem; color:#6c757d; margin:0 0 1.5rem;">Ingresa tus credenciales para continuar.
                </p>

                <form method="POST" action="{{ route('login.login') }}" novalidate id="form-login">
                    @csrf
                    <div class="form-group">
                        <label for="user" class="form-label">Usuario</label>
                        <input id="user" type="text" name="user" autocomplete="username" autofocus
                            class="form-control" placeholder="Tu nombre de usuario y/o DNI"
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
                    <a href="#" onclick="switchTab('register'); return false;"
                        style="color:#0b5ed7; font-weight:600; text-decoration:none;">
                        Regístrate gratis
                    </a>
                </p>
            </div>

            <!-- ══ TAB: REGISTRO ════════════════════════════════════════════ -->
            <div class="tab-pane {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_iddoc', 'reg_nro_doc', 'reg_nombres', 'reg_telefono', 'reg_ubigeo', 'reg_direccion', 'reg_password']) ? 'active' : '' }}"
                id="pane-register" role="tabpanel">

                <h2 style="font-size:1.4rem; font-weight:700; color:#212529; margin:0 0 .3rem;">Crear cuenta</h2>
                <p style="font-size:.875rem; color:#6c757d; margin:0 0 1.5rem;">Regístrate para hacer pedidos en línea y
                    acumular promociones.</p>

                <form method="POST" action="{{ route('login.register') }}" novalidate id="form-register">
                    @csrf

                    <!-- Tipo doc + Nro doc -->
                    <div class="form-row cols-2">
                        <div>
                            <label for="reg_iddoc" class="form-label">Tipo de Doc.</label>
                            <select id="reg_iddoc" name="reg_iddoc"
                                class="form-select {{ $errors->has('reg_iddoc') ? 'is-invalid' : '' }}">
                                <option value="">— Seleccionar —</option>
                                @foreach ($docTypes ?? [] as $dt)
                                    <option value="{{ $dt->id }}"
                                        {{ old('reg_iddoc') == $dt->id ? 'selected' : '' }}>
                                        {{ $dt->codigo }} — {{ $dt->descripcion_documento }}
                                    </option>
                                @endforeach
                            </select>
                            @error('reg_iddoc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="reg_nro_doc" class="form-label">Nro. Documento</label>
                            <input id="reg_nro_doc" type="text" name="reg_nro_doc"
                                class="form-control {{ $errors->has('reg_nro_doc') ? 'is-invalid' : '' }}"
                                placeholder="Ej. 42156789" value="{{ old('reg_nro_doc') }}" maxlength="20">
                            @error('reg_nro_doc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="form-group">
                        <label for="reg_nombres" class="form-label">Nombre completo / Razón Social</label>
                        <input id="reg_nombres" type="text" name="reg_nombres"
                            class="form-control {{ $errors->has('reg_nombres') ? 'is-invalid' : '' }}"
                            placeholder="Juan Pérez García" value="{{ old('reg_nombres') }}" maxlength="255">
                        @error('reg_nombres')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Teléfono -->
                    <div class="form-group">
                        <label for="reg_telefono" class="form-label">Teléfono / Celular</label>
                        <input id="reg_telefono" type="tel" name="reg_telefono"
                            class="form-control {{ $errors->has('reg_telefono') ? 'is-invalid' : '' }}"
                            placeholder="942 123 456" value="{{ old('reg_telefono') }}" maxlength="15">
                        @error('reg_telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ubigeo + Dirección -->
                    <div class="form-row cols-2">
                        <div>
                            <label for="reg_ubigeo" class="form-label">Distrito</label>
                            <select id="reg_ubigeo" name="reg_ubigeo"
                                class="form-select {{ $errors->has('reg_ubigeo') ? 'is-invalid' : '' }}">
                                <option value="">— Seleccionar —</option>
                                <option value="220601" {{ old('reg_ubigeo') === '220601' ? 'selected' : '' }}>Tarapoto
                                </option>
                                <option value="220602" {{ old('reg_ubigeo') === '220602' ? 'selected' : '' }}>Morales
                                </option>
                                <option value="220603" {{ old('reg_ubigeo') === '220603' ? 'selected' : '' }}>La Banda
                                    de Shilcayo</option>
                                <option value="220609" {{ old('reg_ubigeo') === '220609' ? 'selected' : '' }}>Shapaja
                                </option>
                            </select>
                            @error('reg_ubigeo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="reg_direccion" class="form-label">Dirección</label>
                            <input id="reg_direccion" type="text" name="reg_direccion"
                                class="form-control {{ $errors->has('reg_direccion') ? 'is-invalid' : '' }}"
                                placeholder="Jr. Lima 123" value="{{ old('reg_direccion') }}" maxlength="255">
                            @error('reg_direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <button type="button" class="toggle-pass"
                                    onclick="togglePass('reg_password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="pass-strength">
                                <div class="pass-strength-bar">
                                    <div class="pass-strength-fill" id="strength-fill"></div>
                                </div>
                                <div class="pass-strength-label" id="strength-label"></div>
                            </div>
                            @error('reg_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="reg_password_confirmation" class="form-label">Confirmar</label>
                            <div class="input-group">
                                <input id="reg_password_confirmation" type="password"
                                    name="reg_password_confirmation" class="form-control"
                                    placeholder="Repite la contraseña" minlength="8">
                                <button type="button" class="toggle-pass"
                                    onclick="togglePass('reg_password_confirmation', this)">
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
                    <a href="#" onclick="switchTab('login'); return false;"
                        style="color:#0b5ed7; font-weight:600; text-decoration:none;">
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
    <script src="{{ asset('npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (function() {
            'use strict';

            // Tab switching
            window.switchTab = function(tab) {
                document.querySelectorAll('.auth-tab').forEach(t => {
                    t.classList.toggle('active', t.id === 'tab-' + tab);
                    t.setAttribute('aria-selected', t.id === 'tab-' + tab);
                });
                document.querySelectorAll('.tab-pane').forEach(p => {
                    p.classList.toggle('active', p.id === 'pane-' + tab);
                });
            };

            // Toggle password visibility
            window.togglePass = function(inputId, btn) {
                const input = document.getElementById(inputId);
                const icon = btn.querySelector('i');
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                icon.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
            };

            // Password strength meter
            window.updateStrength = function(val) {
                const fill = document.getElementById('strength-fill');
                const label = document.getElementById('strength-label');
                if (!fill || !label) return;

                let score = 0;
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;

                const levels = [{
                        w: '0%',
                        bg: '#e9ecef',
                        txt: ''
                    },
                    {
                        w: '25%',
                        bg: '#dc3545',
                        txt: 'Débil'
                    },
                    {
                        w: '50%',
                        bg: '#fd7e14',
                        txt: 'Regular'
                    },
                    {
                        w: '75%',
                        bg: '#ffc107',
                        txt: 'Buena'
                    },
                    {
                        w: '100%',
                        bg: '#198754',
                        txt: '¡Segura!'
                    },
                ];
                const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 4)];
                fill.style.width = lvl.w;
                fill.style.background = lvl.bg;
                label.textContent = lvl.txt;
                label.style.color = lvl.bg;
            };

            // Loading state on submit
            document.getElementById('form-login')?.addEventListener('submit', function() {
                const btn = document.getElementById('btn-login');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verificando...</span>';
                btn.disabled = true;
            });

            document.getElementById('form-register')?.addEventListener('submit', function() {
                const btn = document.getElementById('btn-register');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Registrando...</span>';
                btn.disabled = true;
            });
        })();
    </script>
</body>

</html>
