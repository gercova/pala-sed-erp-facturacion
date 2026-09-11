@extends('admin.layout')

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="award"></i></div>
                        Programa de Fidelización de Clientes
                    </h1>
                    <div class="small text-muted mt-1">Configura ofertas recurrentes ("Compra X bidones y llévate Y gratis"), revisa el avance de clientes y canjea recompensas.</div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <!-- Métricas del Programa de Fidelidad -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-start-lg border-start-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Clientes en el Programa</div>
                            <div class="h3 mb-0 text-primary">{{ $kpis['total_participantes'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-primary-soft text-primary">
                            <i class="ri-user-star-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border-start-lg border-start-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Premios Listos para Canjear</div>
                            <div class="h3 mb-0 text-success">{{ $kpis['canjes_disponibles'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-success-soft text-success">
                            <i class="ri-gift-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border-start-lg border-start-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Bidones Gratis Canjeados</div>
                            <div class="h3 mb-0 text-info">{{ $kpis['total_premios_entregados'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-info-soft text-info">
                            <i class="ri-hand-heart-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Panel de Configuración de la Promoción (Izquierda) -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold text-dark">
                    <i class="ri-settings-4-line me-1 text-primary"></i> Configuración de la Regla
                </div>
                <div class="card-body">
                    <form id="form-loyalty-settings">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de la Promoción <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" value="{{ $promotion->nombre ?? 'Fidelidad 4+1: Compra 4, ¡el 5to es GRATIS!' }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Meta de Compras (X) <span class="text-danger">*</span></label>
                                <input type="number" name="meta_compras" class="form-control" min="1" max="100" value="{{ $promotion->meta_compras ?? 4 }}" required>
                                <small class="text-muted" style="font-size: 11px;">Ej. 4 bidones</small>
                            </div>

                            <div class="col-6">
                                <label class="form-label small fw-bold">Bidones Gratis (Y) <span class="text-danger">*</span></label>
                                <input type="number" name="bonificacion" class="form-control" min="1" max="10" value="{{ $promotion->bonificacion ?? 1 }}" required>
                                <small class="text-muted" style="font-size: 11px;">Ej. 1 gratis</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Producto Objetivo (Aplica a)</label>
                            <select name="idproducto_objetivo" class="form-select">
                                <option value="">[Cualquier recarga de agua]</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" {{ ($promotion->idproducto_objetivo ?? null) == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->descripcion }} (S/ {{ number_format($prod->precio_venta, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Descripción / Mensaje al Cliente</label>
                            <textarea name="descripcion" class="form-control" rows="3">{{ $promotion->descripcion ?? 'Acumula 4 recargas de bidón y recibe la quinta unidad totalmente gratis.' }}</textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="promo_activo" name="activo" value="1" {{ ($promotion->activo ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-dark" for="promo_activo">Promoción activa en POS y Pedidos QR</label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de Avance de Clientes (Derecha) -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold text-dark d-flex justify-content-between align-items-center">
                    <span><i class="ri-team-line me-1 text-primary"></i> Clientes y Progreso de Compras</span>
                    <span class="small text-muted">Meta actual: <strong>{{ $promotion->meta_compras ?? 4 }} compras = 1 gratis</strong></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-loyalty-clients" class="table table-hover table-sm align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th width="30%">Progreso hacia Premio</th>
                                    <th>Estado</th>
                                    <th>Canjeados</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @include('admin.loyalty.js-loyalty')
@endsection
