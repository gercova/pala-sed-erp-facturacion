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
                        <span class="badge bg-primary-soft text-primary ms-2" id="header-rule-badge">{{ $promotion->rule_label ?? '5+1' }}</span>
                    </h1>
                    <div class="small text-muted mt-1">Configura metas de compra ("{{ $promotion->rule_text ?? '5 más 1' }}"), productos bonificados, historial de reglas y seguimiento de clientes.</div>
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
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-bold text-dark d-flex justify-content-between align-items-center py-3">
                    <span><i class="ri-settings-4-line me-1 text-primary"></i> Configuración de la Regla</span>
                    <span class="badge bg-primary text-white" id="live-rule-badge">{{ $promotion->rule_label ?? '5+1' }}</span>
                </div>
                <div class="card-body">
                    <form id="form-loyalty-settings">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de la Promoción <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="promo_nombre" class="form-control" value="{{ $promotion->nombre ?? 'Fidelidad 5+1: Compra 5, ¡el 6to es GRATIS!' }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Meta de Compras (X) <span class="text-danger">*</span></label>
                                <input type="number" name="meta_compras" id="promo_meta_compras" class="form-control" min="1" max="100" value="{{ $promotion->meta_compras ?? 5 }}" required>
                                <small class="text-muted" style="font-size: 11px;">Compras para ganar premio</small>
                            </div>

                            <div class="col-6">
                                <label class="form-label small fw-bold">Bidones Gratis (Y) <span class="text-danger">*</span></label>
                                <input type="number" name="bonificacion" id="promo_bonificacion" class="form-control" min="1" max="10" value="{{ $promotion->bonificacion ?? 1 }}" required>
                                <small class="text-muted" style="font-size: 11px;">Unidades gratuitas</small>
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
                            <small class="text-muted" style="font-size: 11px;">Producto que el cliente debe comprar</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Producto Bonificado (Premio a entregar)</label>
                            <select name="idproducto_bonificado" class="form-select">
                                <option value="">[Mismo producto objetivo / Recarga estándar]</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" {{ ($promotion->idproducto_bonificado ?? null) == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->descripcion }} (S/ {{ number_format($prod->precio_venta, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Producto gratuito otorgado al cliente</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Descripción / Mensaje al Cliente</label>
                            <textarea name="descripcion" class="form-control" rows="2">{{ $promotion->descripcion ?? 'Acumula recargas de bidón y recibe unidades bonificadas sin costo.' }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Motivo del Cambio (para auditoría)</label>
                            <input type="text" name="motivo_cambio" class="form-control" placeholder="Ej. Actualización acordada a esquema 5+1">
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="promo_activo" name="activo" value="1" {{ ($promotion->activo ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-dark" for="promo_activo">Promoción activa en POS y Portales</label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary shadow-sm" id="btn-save-settings">
                                <i class="ri-save-line me-1"></i> Guardar Cambios Inmediatamente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de Clientes e Historial de Reglas (Derecha) -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#tab-clients" role="tab">
                                <i class="ri-team-line me-1 text-primary"></i> Clientes y Progreso
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#tab-rule-history" role="tab">
                                <i class="ri-history-line me-1 text-info"></i> Historial de Cambios de Reglas
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Tab 1: Clientes y Progreso -->
                        <div class="tab-pane fade show active" id="tab-clients" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-muted">Regla activa: <strong id="summary-meta-text" class="text-primary">{{ $promotion ? $promotion->summary_text : '5 compras = 1 gratis' }}</strong></span>
                                <span class="badge bg-light text-dark border" id="summary-rule-label">{{ $promotion->rule_label ?? '5+1' }}</span>
                            </div>
                            <div class="table-responsive">
                                <table id="table-loyalty-clients" class="table table-hover table-sm align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th>Cliente</th>
                                            <th>Teléfono</th>
                                            <th width="28%">Progreso</th>
                                            <th>Estado</th>
                                            <th>Canjeados</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Historial de Cambios de Reglas -->
                        <div class="tab-pane fade" id="tab-rule-history" role="tabpanel">
                            <div class="small text-muted mb-3">Registro auditable de modificaciones a las metas, bonificaciones y activación del programa de fidelidad.</div>
                            <div class="table-responsive">
                                <table id="table-loyalty-logs" class="table table-hover table-sm align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Meta (X)</th>
                                            <th>Bonif. (Y)</th>
                                            <th>Estado</th>
                                            <th>Motivo</th>
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
    </div>
</div>

@endsection

@section('scripts')
    @include('admin.loyalty.js-loyalty')
@endsection
