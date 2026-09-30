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

            <!-- Global alerts -->
            @if (session('message'))
                <div class="auth-alert danger" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if (session('message_info'))
                <div class="auth-alert info" role="alert" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;">
                    <i class="fas fa-info-circle"></i>
                    <span>{{ session('message_info') }}</span>
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
                    class="auth-tab {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_nro_doc', 'reg_nombres', 'reg_telefono', 'reg_direccion', 'reg_password']) ? 'active' : '' }}"
                    id="tab-register" role="tab" aria-controls="pane-register" aria-selected="false"
                    onclick="switchTab('register')">
                    <i class="fas fa-user-plus me-1"></i> Registrarse
                </button>
            </div>

            <!-- ══ TAB: LOGIN ════════════════════════════════════════════════ -->
            <div class="tab-pane {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_nro_doc', 'reg_nombres', 'reg_telefono', 'reg_direccion', 'reg_password']) ? '' : 'active' }}"
                id="pane-login" role="tabpanel">

                <h2 style="font-size:1.4rem; font-weight:700; color:#212529; margin:0 0 .3rem;">Bienvenido</h2>
                <p style="font-size:.875rem; color:#6c757d; margin:0 0 1.5rem;">
                    @if(session('otp_step'))
                        Ingresa el código de 6 dígitos que enviamos para acceder.
                    @else
                        Ingresa tus credenciales para continuar.
                    @endif
                </p>

                <form method="POST" action="{{ route('login.login') }}" novalidate id="form-login">
                    @csrf

                    @if(session('otp_step'))
                        <input type="hidden" name="user" value="{{ old('user', session('pending_user_dni', '')) }}">
                        <input type="hidden" name="otp_user_id" value="{{ session('otp_user_id') }}">

                        <div class="form-group mb-3">
                            <label for="otp_code" class="form-label fw-bold">Código de 6 dígitos (OTP) <span class="text-danger">*</span></label>
                            <input id="otp_code" type="text" name="otp_code" autocomplete="one-time-code" autofocus
                                class="form-control text-center fs-3 fw-bold" placeholder="000000" maxlength="6" pattern="\d{6}" required>
                            <small class="text-muted d-block text-center mt-1">El código expira en 5 minutos.</small>

                            @if(session('otp_wa_url'))
                                <div class="text-center mt-3">
                                    <a href="{{ session('otp_wa_url') }}" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="fab fa-whatsapp me-1"></i> Ver código en WhatsApp
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="form-group">
                            <label for="user" class="form-label">Usuario o DNI</label>
                            <input id="user" type="text" name="user" autocomplete="username" autofocus
                                class="form-control" placeholder="Tu DNI o nombre de usuario"
                                value="{{ old('user') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                Contraseña
                                @if(($clientAuthMethod ?? 'password') === 'dni')
                                    <span class="badge bg-light text-primary border ms-1" style="font-size:10px;">Opcional para clientes</span>
                                @endif
                            </label>
                            <div class="input-group">
                                <input id="password" type="password" name="password" autocomplete="current-password"
                                    class="form-control" placeholder="{{ ($clientAuthMethod ?? 'password') === 'dni' ? 'Solo si eres operador interno' : '••••••••' }}">
                                <button type="button" class="toggle-pass" onclick="togglePass('password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @if(($clientAuthMethod ?? 'password') === 'dni')
                                <small class="text-muted d-block mt-1" style="font-size:11px;">
                                    <i class="fas fa-bolt text-warning"></i> Modo acceso rápido activo: Clientes pueden acceder solo con su DNI.
                                </small>
                            @elseif(($clientAuthMethod ?? 'password') === 'otp')
                                <small class="text-muted d-block mt-1" style="font-size:11px;">
                                    <i class="fas fa-shield-alt text-info"></i> Modo OTP: Clientes recibirán un código temporal por WhatsApp.
                                </small>
                            @endif
                        </div>
                    @endif

                    <button type="submit" class="btn-auth" id="btn-login" style="margin-top:1rem;">
                        <span>{{ session('otp_step') ? 'Validar Código' : 'Acceder' }}</span>
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
            <div class="tab-pane {{ old('active_tab', session('active_tab')) === 'register' || $errors->hasAny(['reg_nro_doc', 'reg_nombres', 'reg_telefono', 'reg_direccion', 'reg_password']) ? 'active' : '' }}"
                id="pane-register" role="tabpanel">

                <h2 style="font-size:1.4rem; font-weight:700; color:#212529; margin:0 0 .3rem;">Crear cuenta</h2>
                <p style="font-size:.875rem; color:#6c757d; margin:0 0 1.5rem;">Regístrate con tu DNI para hacer pedidos en línea y acumular promociones.</p>

                <form method="POST" action="{{ route('login.register') }}" novalidate id="form-register">
                    @csrf

                    <!-- DNI con Validación de 8 dígitos y Búsqueda RENIEC -->
                    <div class="form-group">
                        <label for="reg_nro_doc" class="form-label">DNI (8 dígitos) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input id="reg_nro_doc" type="text" name="reg_nro_doc"
                                class="form-control {{ $errors->has('reg_nro_doc') ? 'is-invalid' : '' }}"
                                placeholder="Ej. 42156789" value="{{ old('reg_nro_doc') }}" maxlength="8" pattern="\d{8}" required>
                            <button type="button" class="btn btn-outline-primary" id="btn-reniec-search" title="Buscar en RENIEC">
                                <i class="fas fa-search"></i> <span id="btn-reniec-text">RENIEC</span>
                            </button>
                        </div>
                        @error('reg_nro_doc')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small id="reniec-feedback" class="d-none text-muted" style="font-size:11px;"></small>
                    </div>

                    <!-- Nombre completo -->
                    <div class="form-group">
                        <label for="reg_nombres" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                        <input id="reg_nombres" type="text" name="reg_nombres"
                            class="form-control {{ $errors->has('reg_nombres') ? 'is-invalid' : '' }}"
                            placeholder="Juan Pérez García" value="{{ old('reg_nombres') }}" maxlength="255" required>
                        @error('reg_nombres')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Teléfono / Celular -->
                    <div class="form-group">
                        <label for="reg_telefono" class="form-label">Teléfono / WhatsApp <span class="text-danger">*</span></label>
                        <input id="reg_telefono" type="tel" name="reg_telefono"
                            class="form-control {{ $errors->has('reg_telefono') ? 'is-invalid' : '' }}"
                            placeholder="942 123 456" value="{{ old('reg_telefono') }}" maxlength="15" required>
                        @error('reg_telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Distrito + Dirección -->
                    <div class="form-row cols-2">
                        <div>
                            <label for="reg_ubigeo" class="form-label">Distrito</label>
                            <select id="reg_ubigeo" name="reg_ubigeo"
                                class="form-select {{ $errors->has('reg_ubigeo') ? 'is-invalid' : '' }}">
                                <option value="220601" {{ old('reg_ubigeo', '220601') === '220601' ? 'selected' : '' }}>Tarapoto</option>
                                <option value="220602" {{ old('reg_ubigeo') === '220602' ? 'selected' : '' }}>Morales</option>
                                <option value="220603" {{ old('reg_ubigeo') === '220603' ? 'selected' : '' }}>La Banda de Shilcayo</option>
                                <option value="220609" {{ old('reg_ubigeo') === '220609' ? 'selected' : '' }}>Shapaja</option>
                            </select>
                        </div>
                        <div>
                            <label for="reg_direccion" class="form-label">Dirección de entrega <span class="text-danger">*</span></label>
                            <input id="reg_direccion" type="text" name="reg_direccion"
                                class="form-control {{ $errors->has('reg_direccion') ? 'is-invalid' : '' }}"
                                placeholder="Jr. Lima 123" value="{{ old('reg_direccion') }}" maxlength="255" required>
                            @error('reg_direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Referencia + Coordenadas GPS -->
                    <div class="form-row cols-2">
                        <div>
                            <label for="reg_referencia" class="form-label">Referencia</label>
                            <input id="reg_referencia" type="text" name="reg_referencia"
                                class="form-control" placeholder="Frente al parque / Portón blanco"
                                value="{{ old('reg_referencia') }}" maxlength="255">
                        </div>
                        <div>
                            <label for="reg_coordenadas" class="form-label">Ubicación GPS</label>
                            <div class="input-group">
                                <input id="reg_coordenadas" type="text" name="reg_coordenadas"
                                    class="form-control" placeholder="-6.4852,-76.3682"
                                    value="{{ old('reg_coordenadas') }}" maxlength="100">
                                <button type="button" class="btn btn-outline-secondary" id="btn-get-gps" title="Obtener coordenadas actuales">
                                    <i class="fas fa-location-crosshairs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="form-row cols-2">
                        <div>
                            <label for="reg_password" class="form-label">
                                Contraseña
                                @if(($clientAuthMethod ?? 'password') !== 'password')
                                    <span class="badge bg-light text-muted border ms-1" style="font-size:10px;">Opcional</span>
                                @else
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
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
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="reg_password_confirmation" class="form-label">Confirmar Contraseña</label>
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
                    <h6>Crea tu cuenta con tu DNI</h6>
                    <p>Autocompletado seguro con RENIEC y registro rápido de tu dirección.</p>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-box-open"></i></div>
                <div class="feature-text">
                    <h6>Haz tu pedido habitual</h6>
                    <p>Sin formularios repetitivos. Tu dirección se carga automáticamente.</p>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-star"></i></div>
                <div class="feature-text">
                    <h6>Gana promociones de fidelidad</h6>
                    <p>Acumula compras con tu DNI y obtén recargas gratis automáticas.</p>
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

                const levels = [
                    { w: '0%', bg: '#e9ecef', txt: '' },
                    { w: '25%', bg: '#dc3545', txt: 'Débil' },
                    { w: '50%', bg: '#fd7e14', txt: 'Regular' },
                    { w: '75%', bg: '#ffc107', txt: 'Buena' },
                    { w: '100%', bg: '#198754', txt: '¡Segura!' },
                ];
                const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 4)];
                fill.style.width = lvl.w;
                fill.style.background = lvl.bg;
                label.textContent = lvl.txt;
                label.style.color = lvl.bg;
            };

            // Consulta RENIEC para autocompletar nombre
            document.getElementById('btn-reniec-search')?.addEventListener('click', function() {
                const dniInput = document.getElementById('reg_nro_doc');
                const nameInput = document.getElementById('reg_nombres');
                const feedback = document.getElementById('reniec-feedback');
                const btnText = document.getElementById('btn-reniec-text');
                const dni = (dniInput?.value || '').trim();

                if (!/^\d{8}$/.test(dni)) {
                    alert('Por favor ingresa un DNI válido de 8 dígitos.');
                    dniInput?.focus();
                    return;
                }

                if (btnText) btnText.textContent = 'Buscando...';

                fetch("{{ route('login.consultar_dni') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ dni: dni })
                })
                .then(r => r.json())
                .then(data => {
                    if (btnText) btnText.textContent = 'RENIEC';
                    if (data.status && data.nombres) {
                        if (nameInput) nameInput.value = data.nombres;
                        if (feedback) {
                            feedback.className = 'text-success d-block';
                            feedback.textContent = 'Nombre autocompletado con RENIEC.';
                        }
                    } else {
                        if (feedback) {
                            feedback.className = 'text-warning d-block';
                            feedback.textContent = data.msg || 'No se pudo obtener el nombre. Ingrésalo manualmente.';
                        }
                    }
                })
                .catch(() => {
                    if (btnText) btnText.textContent = 'RENIEC';
                    if (feedback) {
                        feedback.className = 'text-warning d-block';
                        feedback.textContent = 'No se pudo consultar el DNI en este momento.';
                    }
                });
            });

            // Geolocalización GPS para coordenadas
            document.getElementById('btn-get-gps')?.addEventListener('click', function() {
                const coordInput = document.getElementById('reg_coordenadas');
                if (!navigator.geolocation) {
                    alert('La geolocalización no es soportada por tu navegador.');
                    return;
                }
                const btn = this;
                btn.disabled = true;
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        btn.disabled = false;
                        const coords = pos.coords.latitude.toFixed(6) + ',' + pos.coords.longitude.toFixed(6);
                        if (coordInput) coordInput.value = coords;
                    },
                    function() {
                        btn.disabled = false;
                        alert('No se pudo obtener tu ubicación actual.');
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            });

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
