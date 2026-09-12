@extends('cliente.layout')
@section('title', 'Mi Panel')
@section('meta_description', 'Panel de control del cliente — pedidos, fidelización e historial.')
@section('extra-styles')
/* ── Loyalty bar ── */
.loyalty-card {
    border: 1px solid #e3e7ef;
}
.loyalty-progress-wrap {
    background: #f1f5f9;
    border-radius: 50px;
    height: 10px;
    overflow: hidden;
    margin: .75rem 0 .4rem;
}
.loyalty-progress-fill {
    height: 100%;
    border-radius: 50px;
    background: #0b5ed7;
    transition: width .6s cubic-bezier(.4,0,.2,1);
}
.loyalty-progress-fill.full {
    background: #16a34a;
}
.star-row { display: flex; gap: .4rem; margin-bottom: .5rem; }
.star { font-size: 1.3rem; }
.star.filled { color: #f59e0b; }
.star.empty  { color: #e2e8f0; }

/* ── Stats ── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}
.stat-card {
    background: #fff;
    border: 1px solid #e3e7ef;
    border-radius: 12px;
    padding: 1.25rem 1rem;
    text-align: center;
}
.stat-value { font-size: 1.8rem; font-weight: 700; color: #0b5ed7; line-height: 1; }
.stat-label { font-size: .78rem; color: #64748b; margin-top: .35rem; font-weight: 500; }

/* ── Order table ── */
.orders-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
.orders-table th {
    text-align: left; padding: .65rem 1rem;
    font-size: .75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .04em; color: #64748b;
    border-bottom: 1px solid #e3e7ef;
}
.orders-table td {
    padding: .8rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
.orders-table tr:last-child td { border-bottom: none; }
.orders-table tr:hover td { background: #f8fafc; }

.btn-track {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .75rem; border-radius: 8px;
    font-size: .8rem; font-weight: 600;
    background: #eff6ff; color: #1d4ed8;
    border: 1px solid #bfdbfe; text-decoration: none;
    transition: all .15s;
}
.btn-track:hover { background: #dbeafe; }

/* ── Free reward badge ── */
.reward-banner {
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 1.75rem;
}
.reward-icon {
    width: 48px; height: 48px; border-radius: 50%;
    background: #dcfce7; display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
}
.reward-text h5 { font-size: 1rem; font-weight: 700; color: #14532d; margin: 0 0 .2rem; }
.reward-text p  { font-size: .85rem; color: #166534; margin: 0; }
@endsection

@section('content')
<div class="page-header">
    <h1>Hola, {{ ucwords(strtolower(explode(' ', $client->nombres)[0])) }} 👋</h1>
    <p>Bienvenido/a a tu panel. Aquí puedes ver tus pedidos y acumular tus puntos de fidelización.</p>
</div>

{{-- ── Premio disponible ── --}}
@if($loyalty['reward_eligible'] ?? false)
    <div class="reward-banner">
        <div class="reward-icon">🎁</div>
        <div class="reward-text">
            <h5>¡Tienes un pedido gratis disponible!</h5>
            <p>Tu próxima recarga de bidón tiene <strong>100% de descuento</strong>. ¡Haz tu pedido ahora!</p>
        </div>
        <a href="{{ route('cliente.order') }}" class="nav-link-btn btn-order ms-auto" style="white-space:nowrap; text-decoration:none;">
            <i class="ri-add-circle-line"></i> Pedir ahora
        </a>
    </div>
@endif

{{-- ── Stats rápidas ── --}}
@php
    $totalPedidos   = $orders->count();
    $entregados     = $orders->where('estado', 'entregado')->count();
    $pendientes     = $orders->whereIn('estado', ['pendiente', 'en_camino'])->count();
    $totalGastado   = $orders->where('estado', 'entregado')->sum('total');
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
@if($loyalty['has_promotion'] ?? false)
@php
    $accumulated = $loyalty['accumulated'];
    $target      = $loyalty['target'];
    $pct         = $target > 0 ? min(100, round($accumulated / $target * 100)) : 0;
    $full        = $loyalty['reward_eligible'];
@endphp
<div class="p-card loyalty-card" style="margin-bottom:1.75rem;">
    <div class="p-card-header">
        <i class="ri-gift-line"></i>
        Programa de Fidelización — {{ $loyalty['promotion_name'] }}
    </div>
    <div class="star-row">
        @for($s = 1; $s <= $target; $s++)
            <span class="star {{ $s <= $accumulated ? 'filled' : 'empty' }}">★</span>
        @endfor
    </div>
    <div class="loyalty-progress-wrap">
        <div class="loyalty-progress-fill {{ $full ? 'full' : '' }}" style="width: {{ $pct }}%;"></div>
    </div>
    <div style="display:flex; justify-content:space-between; font-size:.82rem; color:#64748b;">
        <span>{{ $accumulated }} de {{ $target }} recargas acumuladas</span>
        @if(!$full)
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
        <a href="{{ route('cliente.order') }}" class="nav-link-btn btn-order" style="text-decoration:none; padding:.35rem .8rem; font-size:.8rem;">
            <i class="ri-add-line"></i> Nuevo
        </a>
    </div>

    @if($orders->isEmpty())
        <div style="text-align:center; padding:3rem 1rem;">
            <i class="ri-box-3-line" style="font-size:3rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
            <p style="color:#94a3b8; margin:0 0 1rem; font-size:.95rem;">Aún no tienes pedidos registrados.</p>
            <a href="{{ route('cliente.order') }}" class="nav-link-btn btn-order" style="text-decoration:none; width:fit-content; margin:0 auto;">
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
                    @foreach($orders as $order)
                    <tr>
                        <td>
                            <span style="font-family:monospace; font-weight:600; color:#1e40af;">{{ $order->codigo_orden }}</span>
                        </td>
                        <td>
                            <span>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</span>
                            @if($order->fecha_programada)
                                <br><small style="color:#94a3b8;">Prog: {{ \Carbon\Carbon::parse($order->fecha_programada)->format('d/m/Y') }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $estadoMap = [
                                    'pendiente'  => ['class'=>'badge-pendiente',  'icon'=>'ri-time-line',    'label'=>'Pendiente'],
                                    'en_camino'  => ['class'=>'badge-en_camino',  'icon'=>'ri-truck-line',   'label'=>'En camino'],
                                    'entregado'  => ['class'=>'badge-entregado',  'icon'=>'ri-check-line',   'label'=>'Entregado'],
                                    'cancelado'  => ['class'=>'badge-cancelado',  'icon'=>'ri-close-line',   'label'=>'Cancelado'],
                                ];
                                $e = $estadoMap[$order->estado] ?? ['class'=>'badge-pendiente','icon'=>'ri-question-line','label'=>ucfirst($order->estado)];
                            @endphp
                            <span class="badge-estado {{ $e['class'] }}">
                                <i class="{{ $e['icon'] }}"></i> {{ $e['label'] }}
                            </span>
                        </td>
                        <td style="text-transform:capitalize;">{{ $order->metodo_pago }}</td>
                        <td style="text-align:right; font-weight:600;">S/ {{ number_format($order->total, 2) }}</td>
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
