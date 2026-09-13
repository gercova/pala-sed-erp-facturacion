<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi Portal') — {{ $business->nombre_comercial ?? ($business->razon_social ?? 'Portal Cliente') }}
    </title>
    <meta name="description" content="@yield('meta_description', 'Portal de clientes para gestionar pedidos de agua purificada.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/remixicon.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/img/favicon-white.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('css/client-layout.css') }}">
    @yield('extra-styles')
    @yield('styles')
</head>

<body>

    <!-- ── Navbar ── -->
    <nav class="portal-nav">
        <div class="container">
            <a href="{{ route('cliente.dashboard') }}" class="nav-brand">
                @if (!empty($business?->logo))
                    <img src="{{ asset('files/logos/' . $business->logo) }}" alt="Logo">
                @endif
                <span>{{ $business->nombre_comercial ?? ($business->razon_social ?? 'Mi Portal') }}</span>
            </a>

            <div class="nav-actions">
                <a href="{{ route('cliente.dashboard') }}"
                    class="nav-link-btn d-none d-md-inline-flex {{ request()->routeIs('cliente.dashboard') ? 'active' : '' }}">
                    <i class="ri-home-line"></i> Mis Pedidos
                </a>
                <a href="{{ route('cliente.order') }}" class="nav-link-btn btn-order">
                    <i class="ri-add-circle-line"></i> Nuevo Pedido
                </a>
                <div class="nav-user d-none d-sm-flex">
                    <div class="avatar">{{ strtoupper(substr(Auth::user()->nombres ?? Auth::user()->user, 0, 1)) }}
                    </div>
                    <span>{{ Str::limit(Auth::user()->nombres ?? Auth::user()->user, 18) }}</span>
                </div>
                <a href="{{ route('cliente.logout') }}" class="nav-link-btn btn-logout"
                    onclick="return confirm('¿Cerrar sesión?')" title="Salir">
                    <i class="ri-logout-box-line"></i>
                    <span class="d-none d-md-inline">Salir</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- ── Flash messages ── -->
    @if (session('message_welcome'))
        <div class="container" style="margin-top:1rem;">
            <div class="p-alert success">
                <i class="ri-checkbox-circle-line" style="font-size:1.1rem; flex-shrink:0;"></i>
                <span>{{ session('message_welcome') }}</span>
            </div>
        </div>
    @endif
    @if (session('message'))
        <div class="container" style="margin-top:1rem;">
            <div class="p-alert warning">
                <i class="ri-error-warning-line" style="font-size:1.1rem; flex-shrink:0;"></i>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- ── Content ── -->
    <main class="portal-page">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <script src="{{ asset('js/jquery-3.6.4.min.js') }}"></script>
    <script src="{{ asset('npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>

    @yield('scripts')
</body>

</html>
