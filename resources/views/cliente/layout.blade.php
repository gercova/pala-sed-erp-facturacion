<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi Portal') — {{ $business->nombre_comercial ?? $business->razon_social ?? 'Portal Cliente' }}</title>
    <meta name="description" content="@yield('meta_description', 'Portal de clientes para gestionar pedidos de agua purificada.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/img/favicon-white.ico') }}" type="image/x-icon">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --primary: #0b5ed7;
            --primary-light: #e8f0fd;
            --surface: #f4f6fa;
            --card-bg: #ffffff;
            --border: #e3e7ef;
            --text: #1e293b;
            --muted: #64748b;
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
            margin: 0;
        }

        /* ── Navbar ── */
        .portal-nav {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: .875rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .portal-nav .container { display: flex; align-items: center; gap: 1rem; }
        .nav-brand {
            display: flex; align-items: center; gap: .75rem;
            text-decoration: none; color: var(--text); font-weight: 700;
            font-size: 1rem; flex: 1;
        }
        .nav-brand img { height: 32px; width: 32px; object-fit: contain; border-radius: 8px; }
        .nav-actions { display: flex; align-items: center; gap: .5rem; }

        .nav-link-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem .9rem; border-radius: 8px;
            font-size: .85rem; font-weight: 500;
            text-decoration: none; color: var(--muted);
            transition: all .15s;
            border: 1px solid transparent;
        }
        .nav-link-btn:hover { background: var(--primary-light); color: var(--primary); }
        .nav-link-btn.active { background: var(--primary-light); color: var(--primary); border-color: #c3d8f8; }
        .nav-link-btn.btn-order {
            background: var(--primary); color: #fff;
            border-color: var(--primary); font-weight: 600;
        }
        .nav-link-btn.btn-order:hover { background: #0a53be; }
        .nav-link-btn.btn-logout { color: #dc2626; }
        .nav-link-btn.btn-logout:hover { background: #fff5f5; border-color: #fecaca; }

        .nav-user {
            display: flex; align-items: center; gap: .5rem;
            padding: .4rem .75rem; border-radius: 8px;
            background: var(--surface); border: 1px solid var(--border);
            font-size: .82rem; font-weight: 500; color: var(--muted);
        }
        .nav-user .avatar {
            width: 26px; height: 26px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 700; flex-shrink: 0;
        }

        /* ── Page ── */
        .portal-page { padding: 2rem 0 3rem; }
        .page-header {
            margin-bottom: 1.75rem;
        }
        .page-header h1 {
            font-size: 1.5rem; font-weight: 700; color: var(--text); margin: 0 0 .25rem;
        }
        .page-header p { font-size: .875rem; color: var(--muted); margin: 0; }

        /* ── Cards ── */
        .p-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(15,23,42,.04);
        }
        .p-card-header {
            display: flex; align-items: center; gap: .6rem;
            font-size: .8rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: var(--muted);
            margin-bottom: 1.25rem; padding-bottom: .75rem;
            border-bottom: 1px solid var(--border);
        }
        .p-card-header i { color: var(--primary); font-size: 1rem; }

        /* ── Badge states ── */
        .badge-estado {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .3rem .65rem; border-radius: 20px;
            font-size: .75rem; font-weight: 600;
        }
        .badge-pendiente  { background:#fef9c3; color:#854d0e; }
        .badge-en_camino  { background:#dbeafe; color:#1e40af; }
        .badge-entregado  { background:#dcfce7; color:#14532d; }
        .badge-cancelado  { background:#fee2e2; color:#991b1b; }

        /* ── Alerts ── */
        .p-alert {
            padding: .85rem 1rem; border-radius: 10px;
            font-size: .875rem; margin-bottom: 1.25rem;
            display: flex; align-items: flex-start; gap: .6rem;
        }
        .p-alert.success { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
        .p-alert.info    { background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; }
        .p-alert.warning { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }

        /* ── Forms ── */
        .p-label {
            display: block; font-size: .78rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .04em;
            color: var(--muted); margin-bottom: .35rem;
        }
        .p-input {
            width: 100%; padding: .7rem 1rem;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: .9rem; font-family: inherit;
            background: #f8fafc; color: var(--text);
            transition: all .2s; outline: none;
        }
        .p-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(11,94,215,.1);
            background: #fff;
        }
        .p-input.is-invalid { border-color: var(--danger); }

        @yield('extra-styles')
    </style>

    @yield('styles')
</head>
<body>

<!-- ── Navbar ── -->
<nav class="portal-nav">
    <div class="container">
        <a href="{{ route('cliente.dashboard') }}" class="nav-brand">
            @if(!empty($business?->logo))
                <img src="{{ asset('files/logos/' . $business->logo) }}" alt="Logo">
            @endif
            <span>{{ $business->nombre_comercial ?? $business->razon_social ?? 'Mi Portal' }}</span>
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
                <div class="avatar">{{ strtoupper(substr(Auth::user()->nombres ?? Auth::user()->user, 0, 1)) }}</div>
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
@if(session('message_welcome'))
    <div class="container" style="margin-top:1rem;">
        <div class="p-alert success">
            <i class="ri-checkbox-circle-line" style="font-size:1.1rem; flex-shrink:0;"></i>
            <span>{{ session('message_welcome') }}</span>
        </div>
    </div>
@endif
@if(session('message'))
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

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@yield('scripts')
</body>
</html>
