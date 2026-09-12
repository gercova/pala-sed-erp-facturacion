@extends('cliente.layout')
@section('title', 'Mi Panel')
@section('meta_description', 'Panel de control del cliente — pedidos, fidelización e historial.')
@section('extra-styles')
    <link rel="stylesheet" href="{{ asset('css/loyalty.css') }}">
@endsection

@section('content')
    <div class="page-header">
        <h1>Hola, {{ ucwords(strtolower(explode(' ', $client->nombres)[0])) }} 👋</h1>
        <p>Bienvenido/a a tu panel. Aquí puedes ver tus pedidos y acumular tus puntos de fidelización.</p>
    </div>

    {{-- ── Premio disponible ── --}}
    @if ($loyalty['reward_eligible'] ?? false)
        <div class="reward-banner">
            <div class="reward-icon">🎁</div>
            <div class="reward-text">
                <h5>¡Tienes un pedido gratis disponible!</h5>
                <p>Tu próxima recarga de bidón tiene <strong>100% de descuento</strong>. ¡Haz tu pedido ahora!</p>
            </div>
            <a href="{{ route('cliente.order') }}" class="nav-link-btn btn-order ms-auto"
                style="white-space:nowrap; text-decoration:none;">
                <i class="ri-add-circle-line"></i> Pedir ahora
            </a>
        </div>
    @endif

    {{-- ── Stats rápidas ── --}}
    @php
        $totalPedidos = $orders->count();
        $entregados = $orders->where('estado', 'entregado')->count();
        $pendientes = $orders->whereIn('estado', ['pendiente', 'en_camino'])->count();
        $totalGastado = $orders->where('estado', 'entregado')->sum('total');
    @endphp
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $totalPedidos }}</div>
            <div class="stat-label">Pedidos totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#16a34a;">{{ $entregados }}</div>
            <div class="stat-label">Entregados</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#d97706;">{{ $pendientes }}</div>
            <div class="stat-label">En curso</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:1.35rem;">S/ {{ number_format($totalGastado, 2) }}</div>
            <div class="stat-label">Total consumido</div>
        </div>
    </div>

    {{-- ── Fidelización ── --}}
    @if ($loyalty['has_promotion'] ?? false)
        @php
            $accumulated = $loyalty['accumulated'];
            $target = $loyalty['target'];
            $pct = $target > 0 ? min(100, round(($accumulated / $target) * 100)) : 0;
            $full = $loyalty['reward_eligible'];
        @endphp
        <div class="p-card loyalty-card" style="margin-bottom:1.75rem;">
            <div class="p-card-header">
                <i class="ri-gift-line"></i>
                Programa de Fidelización — {{ $loyalty['promotion_name'] }}
            </div>
            <div class="star-row">
                @for ($s = 1; $s <= $target; $s++)
                    <span class="star {{ $s <= $accumulated ? 'filled' : 'empty' }}">★</span>
                @endfor
            </div>
            <div class="loyalty-progress-wrap">
                <div class="loyalty-progress-fill {{ $full ? 'full' : '' }}" style="width: {{ $pct }}%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:.82rem; color:#64748b;">
                <span>{{ $accumulated }} de {{ $target }} recargas acumuladas</span>
                @if (!$full)
                    <span>Faltan <strong>{{ $loyalty['remaining_to_free'] }}</strong> para el siguiente gratis</span>
                @else
                    <span style="color:#16a34a; font-weight:700;">🎁 ¡Premio listo para canjear!</span>
                @endif
            </div>
            <p style="font-size:.82rem; color:#94a3b8; margin:.75rem 0 0;">
                Premios canjeados hasta ahora: <strong>{{ $loyalty['rewards_claimed'] }}</strong>
            </p>
        </div>
    @endif

    {{-- ── Historial de pedidos ── --}}
    <div class="p-card">
        <div class="p-card-header" style="justify-content:space-between;">
            <span><i class="ri-list-check"></i> Mis Pedidos</span>
            <a href="{{ route('cliente.order') }}" class="nav-link-btn btn-order"
                style="text-decoration:none; padding:.35rem .8rem; font-size:.8rem;">
                <i class="ri-add-line"></i> Nuevo
            </a>
        </div>

        @if ($orders->isEmpty())
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="ri-box-3-line" style="font-size:3rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:#94a3b8; margin:0 0 1rem; font-size:.95rem;">Aún no tienes pedidos registrados.</p>
                <a href="{{ route('cliente.order') }}" class="nav-link-btn btn-order"
                    style="text-decoration:none; width:fit-content; margin:0 auto;">
                    <i class="ri-add-circle-line"></i> Hacer mi primer pedido
                </a>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th style="text-align:right;">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>
                                    <span
                                        style="font-family:monospace; font-weight:600; color:#1e40af;">{{ $order->codigo_orden }}</span>
                                </td>
                                <td>
                                    <span>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</span>
                                    @if ($order->fecha_programada)
                                        <br><small style="color:#94a3b8;">Prog:
                                            {{ \Carbon\Carbon::parse($order->fecha_programada)->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $estadoMap = [
                                            'pendiente' => [
                                                'class' => 'badge-pendiente',
                                                'icon' => 'ri-time-line',
                                                'label' => 'Pendiente',
                                            ],
                                            'en_camino' => [
                                                'class' => 'badge-en_camino',
                                                'icon' => 'ri-truck-line',
                                                'label' => 'En camino',
                                            ],
                                            'entregado' => [
                                                'class' => 'badge-entregado',
                                                'icon' => 'ri-check-line',
                                                'label' => 'Entregado',
                                            ],
                                            'cancelado' => [
                                                'class' => 'badge-cancelado',
                                                'icon' => 'ri-close-line',
                                                'label' => 'Cancelado',
                                            ],
                                        ];
                                        $e = $estadoMap[$order->estado] ?? [
                                            'class' => 'badge-pendiente',
                                            'icon' => 'ri-question-line',
                                            'label' => ucfirst($order->estado),
                                        ];
                                    @endphp
                                    <span class="badge-estado {{ $e['class'] }}">
                                        <i class="{{ $e['icon'] }}"></i> {{ $e['label'] }}
                                    </span>
                                </td>
                                <td style="text-transform:capitalize;">{{ $order->metodo_pago }}</td>
                                <td style="text-align:right; font-weight:600;">S/ {{ number_format($order->total, 2) }}
                                </td>
                                <td>
                                    <a href="{{ route('cliente.tracking', $order->codigo_orden) }}" class="btn-track">
                                        <i class="ri-map-pin-2-line"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
