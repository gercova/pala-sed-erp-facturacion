{{-- MODAL APERTURA DE CAJA --}}
<div class="modal fade" id="modalArchingCash" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="ri-lock-unlock-line me-2 text-primary"></i>Aperturar caja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form_save" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Caja asignada</label>
                        <input type="text" class="form-control bg-light" value="{{ $assignedCash?->descripcion ?? 'Sin caja asignada' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Almacen activo</label>
                        <input type="text" class="form-control bg-light" value="{{ $currentWarehouse?->descripcion ?? 'No seleccionado' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Responsable</label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user()->nombres }}" readonly>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Monto inicial base (S/) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">{{ $signo ?? 'S/' }}</span>
                            <input type="number" class="form-control form-control-lg text-center fw-bold" name="monto_inicial" min="0" step="0.01" value="0.00" required>
                        </div>
                        <small class="text-muted d-block mt-2">Monto en efectivo con el que se inicia el turno en la gaveta.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-save">
                    <span class="text-save"><i class="ri-check-line me-1"></i>Aperturar caja</span>
                    <span class="text-saving d-none"><i class="ri-loader-4-line spin me-1"></i>Aperturando...</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETALLE Y RECONCILIACION DE CAJA --}}
<div class="modal fade" id="modalDetailArchingCash" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0">
                        <i class="ri-file-chart-line me-2 text-primary"></i>Cuadre y Reconciliación de Caja
                    </h5>
                    <small class="text-muted">Detalle financiero, balance de bidones y operaciones del turno</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="detail_arching_cash_id">

                {{-- Fila informativa general --}}
                <div class="arching-detail-grid mb-3">
                    <div class="arching-detail-card">
                        <small>Caja</small>
                        <strong id="detail_cash_name">-</strong>
                    </div>
                    <div class="arching-detail-card">
                        <small>Almacen</small>
                        <strong id="detail_cash_warehouse">-</strong>
                    </div>
                    <div class="arching-detail-card">
                        <small>Responsable</small>
                        <strong id="detail_cash_user">-</strong>
                    </div>
                    <div class="arching-detail-card">
                        <small>Estado</small>
                        <strong id="detail_cash_status">-</strong>
                    </div>
                </div>

                {{-- Modulo 1: Resumen Financiero --}}
                <div class="card border mb-3 shadow-none">
                    <div class="card-header bg-light py-2 px-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark small text-uppercase">
                            <i class="ri-money-dollar-circle-line me-1 text-primary"></i>Resumen Financiero y Efectivo en Gaveta
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="arching-summary-grid">
                            <div class="arching-summary-card">
                                <small>Monto Apertura (Base)</small>
                                <strong id="detail_opening_amount">S/ 0.00</strong>
                            </div>
                            <div class="arching-summary-card">
                                <small>Ventas Vigentes</small>
                                <strong id="detail_sales_count">0</strong>
                            </div>
                            <div class="arching-summary-card">
                                <small>Total Ventas</small>
                                <strong id="detail_sales_total">S/ 0.00</strong>
                            </div>
                            <div class="arching-summary-card is-cash">
                                <small class="text-primary fw-semibold">Efectivo Cobrado en Turno</small>
                                <strong id="detail_cash_total" class="text-primary">S/ 0.00</strong>
                            </div>
                            <div class="arching-summary-card">
                                <small>Cobro Digital / Tarjetas</small>
                                <strong id="detail_digital_total">S/ 0.00</strong>
                            </div>
                            <div class="arching-summary-card is-final">
                                <small class="text-success fw-bold">Efectivo Físico Esperado</small>
                                <strong id="detail_expected_cash" class="text-success">S/ 0.00</strong>
                            </div>
                            <div class="arching-summary-card">
                                <small>Total Bruto Facturado</small>
                                <strong id="detail_gross_total">S/ 0.00</strong>
                            </div>
                            <div class="arching-summary-card">
                                <small>Comprobantes Anulados</small>
                                <strong id="detail_annulled_count" class="text-danger">0</strong>
                            </div>
                            <div class="arching-summary-card">
                                <small>Monto Anulaciones</small>
                                <strong id="detail_annulled_total" class="text-danger">S/ 0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modulo 2: Cuadre de Bidones / Envases (4 buckets) & Operaciones --}}
                <div class="row g-3 mb-3">
                    <div class="col-lg-7">
                        <div class="card border shadow-none h-100">
                            <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark small text-uppercase">
                                    <i class="ri-recycle-line me-1 text-success"></i>Cuadre de Envases / Bidones (20L)
                                </h6>
                                <span class="badge bg-secondary-subtle text-secondary small">Balance del turno</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2 text-center">
                                    <div class="col-3">
                                        <div class="jug-stat-box jug-aptos">
                                            <div class="jug-count" id="detail_jugs_intact">0</div>
                                            <div class="jug-label">Aptos</div>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">Intactos devueltos</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="jug-stat-box jug-danados">
                                            <div class="jug-count" id="detail_jugs_damaged">0</div>
                                            <div class="jug-label">Dañados</div>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">Rotos devueltos</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="jug-stat-box jug-prestados">
                                            <div class="jug-count" id="detail_jugs_loaned">0</div>
                                            <div class="jug-label">Prestados</div>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">En clientes</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="jug-stat-box jug-vendidos">
                                            <div class="jug-count" id="detail_jugs_sold">0</div>
                                            <div class="jug-label">Vendidos</div>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">Facturados</small>
                                        </div>
                                    </div>
                                </div>
                                <div id="detail_damage_cost_row" class="mt-2 text-danger small text-end fw-semibold d-none">
                                    Cobro registrado por daños: <span id="detail_damage_cost">S/ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border shadow-none h-100">
                            <div class="card-header bg-light py-2 px-3 border-bottom">
                                <h6 class="mb-0 fw-bold text-dark small text-uppercase">
                                    <i class="ri-truck-line me-1 text-primary"></i>Operaciones y Entregas
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light mb-2">
                                    <span class="small text-muted"><i class="ri-file-list-3-line me-1"></i>Pedidos Registrados:</span>
                                    <strong class="text-dark" id="detail_total_orders">0</strong>
                                </div>
                                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light mb-2">
                                    <span class="small text-muted"><i class="ri-checkbox-circle-line me-1 text-success"></i>Entregas Completadas:</span>
                                    <strong class="text-success" id="detail_total_deliveries">0</strong>
                                </div>
                                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                                    <span class="small text-muted"><i class="ri-money-dollar-circle-line me-1 text-primary"></i>Recaudado en Reparto:</span>
                                    <strong class="text-primary" id="detail_deliveries_collected">S/ 0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modulo 3: Medios de Pago y Tabla de Movimientos --}}
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="card border shadow-none h-100">
                            <div class="card-header bg-light py-2 px-3 border-bottom">
                                <h6 class="mb-0 fw-bold text-dark small text-uppercase">Medios de pago</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">Método</th>
                                                <th class="text-end pe-3">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="arching_payments_body">
                                            <tr>
                                                <td colspan="2" class="text-center text-muted py-4">Sin movimientos registrados.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card border shadow-none h-100">
                            <div class="card-header bg-light py-2 px-3 border-bottom">
                                <h6 class="mb-0 fw-bold text-dark small text-uppercase">Movimientos de ventas en turno</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table id="archingMovementsTable" class="table table-hover table-sm mb-0 w-100">
                                        <thead>
                                            <tr>
                                                <th width="14%" class="text-center">Fecha</th>
                                                <th width="12%" class="text-center">Hora</th>
                                                <th width="18%" class="text-center">Documento</th>
                                                <th>Cliente</th>
                                                <th width="16%" class="text-center">Pago</th>
                                                <th width="14%" class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-dark" id="btn-print-summary">
                    <i class="ri-printer-line me-1"></i>Imprimir Ticket
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-danger d-none" id="btn-detail-close-cash">
                        <i class="ri-lock-2-line me-1"></i>Proceder al Cierre de Caja
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CIERRE DE CAJA (CONTEO FISICO Y CONFIRMACION) --}}
<div class="modal fade" id="modalCloseArchingCash" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="ri-lock-2-line me-2"></i>Cierre y Arqueo Final de Caja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form_close_cash" autocomplete="off">
                    @csrf
                    <input type="hidden" name="id" id="close_cash_id">

                    <div class="bg-light rounded p-3 mb-3 border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Caja a cerrar:</span>
                            <strong class="text-dark" id="close_cash_name">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Responsable:</span>
                            <span class="fw-semibold text-dark" id="close_cash_user">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Monto inicial base:</span>
                            <span class="fw-semibold text-dark" id="close_cash_opening">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="card border-primary border-opacity-25 bg-primary-subtle p-3 mb-3 text-center">
                        <small class="text-primary fw-semibold text-uppercase d-block mb-1">Efectivo Físico Esperado en Gaveta</small>
                        <div class="h3 fw-bold text-primary mb-0" id="close_cash_expected">S/ 0.00</div>
                        <small class="text-muted mt-1">(Monto Inicial + Efectivo Cobrado en Turno)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Efectivo Físico Contado en Caja (S/) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">{{ $signo ?? 'S/' }}</span>
                            <input type="number" class="form-control form-control-lg text-center fw-bold text-dark" id="close_cash_counted" name="monto_real" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <small class="text-muted mt-1 d-block">Ingrese el dinero físico real que tiene actualmente en caja.</small>
                    </div>

                    {{-- Caja de cálculo de diferencia en vivo --}}
                    <div id="close_cash_diff_alert" class="alert alert-secondary py-2 px-3 text-center mb-0 d-none">
                        <span id="close_cash_diff_text" class="fw-semibold small">Diferencia: S/ 0.00</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-confirm-close">
                    <span class="text-close-btn"><i class="ri-lock-2-line me-1"></i>Confirmar y Cerrar Caja</span>
                    <span class="text-closing-btn d-none"><i class="ri-loader-4-line spin me-1"></i>Cerrando caja...</span>
                </button>
            </div>
        </div>
    </div>
</div>
