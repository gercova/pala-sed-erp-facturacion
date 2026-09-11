<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - EasyStock</title>

    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/img/favicon-white.ico') }}" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-wrapper {
            display: flex;
            width: 900px;
            max-width: 95vw;
            height: 550px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.06), 0 5px 15px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .login-form-area {
            flex: 1;
            padding: 3rem 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-info-area {
            flex: 1;
            background: linear-gradient(135deg, #e9ecef 0%, #f1f3f5 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        .login-info-area::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%230b5ed7" fill-opacity="0.03"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
            opacity: 0.8;
            pointer-events: none;
        }
        
        .brand-logo-container {
            width: 50px;
            height: 50px;
            background: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        .brand-logo-container img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            background-color: #fff;
            border-color: #0b5ed7;
            box-shadow: 0 0 0 0.25rem rgba(11, 94, 215, 0.15);
        }
        .input-group-text {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .btn-primary-custom {
            background-color: #0b5ed7;
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background-color: #0a53be;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(11, 94, 215, 0.3);
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }
        .feature-icon {
            width: 40px;
            height: 40px;
            background-color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: #0b5ed7;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            flex-shrink: 0;
        }
        .feature-text h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: #212529;
        }
        .feature-text p {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0;
        }
        .login-info-area > * {
            position: relative;
            z-index: 2;
        }
        @media (max-width: 768px) {
            .login-info-area {
                display: none;
            }
            .login-wrapper {
                height: auto;
                max-width: 400px;
            }
            .login-form-area {
                padding: 2.5rem 2rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <!-- Form Area -->
        <div class="login-form-area">
            <div class="brand-logo-container">
                @if (empty($logo))
                    <img src="{{ asset('files/empty_logo.png') }}" alt="Logo EasyStock">
                @else
                    <img src="{{ asset('files/logos/' . $logo) }}" alt="Logo EasyStock">
                @endif
            </div>
            
            <h2 class="fw-bold mb-1" style="color: #212529;">Bienvenido</h2>
            <p class="text-muted mb-4" style="font-size: 0.95rem;">Ingresa tus credenciales para continuar.</p>

            <form method="POST" action="{{ route('login.login') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="user" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Usuario</label>
                    <input
                        id="user"
                        type="text"
                        name="user"
                        class="form-control @if(session('message')) is-invalid @endif"
                        placeholder="Ingresa tu nombre de usuario"
                        required
                        autocomplete="username"
                        autofocus
                        value="testuser"
                    >
                    @if (session('message'))
                        <div class="invalid-feedback">{{ session('message') }}</div>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Contraseña</label>
                    <div class="input-group">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control border-end-0"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            value="Test1234$$."
                        >
                        <button class="btn border border-start-0 bg-light" type="button" id="togglePassword" style="border-radius: 0 10px 10px 0; border-color:#dee2e6;">
                            <i class="fas fa-eye text-muted"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-primary-custom w-100 text-white d-flex justify-content-center align-items-center">
                    <span>Acceder</span>
                    <i class="fas fa-arrow-right ms-2" style="font-size: 0.85rem;"></i>
                </button>
            </form>

            <div class="mt-4 text-center text-muted" style="font-size: 0.8rem;">
                &copy; {{ date('Y') }} EasyStock POS.
            </div>
        </div>

        <!-- Info Area -->
        <div class="login-info-area">
            <h3 class="fw-bold mb-2" style="color:#212529;">Agiliza tus operaciones.</h3>
            <p class="text-muted mb-4" style="font-size:0.95rem;">Un espacio diseñado para hacer tu inventario rápido, preciso y confiable.</p>

            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                <div class="feature-text">
                    <h5>Rápido y Fluido</h5>
                    <p>Punto de venta súper optimizado para alta demanda.</p>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="feature-text">
                    <h5>Información Clara</h5>
                    <p>Toma decisiones con reportes detallados y concisos.</p>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="feature-text">
                    <h5>Respaldo Seguro</h5>
                    <p>La información siempre disponible y protegida.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('ajax/libs/font-awesome/6.3.0/js/all.min.js') }}" defer></script>
    <script src="{{ asset('npm/bootstrap%405.2.3/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (function () {
            const btn = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            if (!btn || !input) return;

            btn.addEventListener('click', function () {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                this.innerHTML = isPassword ? '<i class="fas fa-eye-slash text-muted"></i>' : '<i class="fas fa-eye text-muted"></i>';
            });
        })();
    </script>
</body>
</html>