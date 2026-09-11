<div class="modal fade" id="modalArchingCash" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-lock-unlock-line me-2"></i>Aperturar caja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="form_save" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Caja asignada</label>
                        <input type="text" class="form-control" value="{{ $assignedCash?->descripcion ?? 'Sin caja asignada' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Almacen activo</label>
                        <input type="text" class="form-control" value="{{ $currentWarehouse?->descripcion ?? 'No seleccionado' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->nombres }}" readonly>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Monto inicial <span class="text-danger">*</span></label>
                        <input type="number" class="form-control text-center" name="monto_inicial" min="0" step="0.01" value="0.00">
                        <small class="text-muted d-block mt-2">Este monto sera la base inicial del arqueo de caja.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-save">
                    <span class="text-save">Aperturar caja</span>
                    <span class="text-saving d-none">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailArchingCash" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-file-list-3-line me-2"></i>Resumen de caja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="detail_arching_cash_id">

                <div class="arching-detail-grid mb-4">
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

                <div class="arching-summary-grid mb-4">
                    <div class="arching-summary-card">
                        <small>Monto inicial</small>
                        <strong id="detail_opening_amount">S/ 0.00</strong>
                    </div>
                    <div class="arching-summary-card">
                        <small>Ventas vigentes</small>
                        <strong id="detail_sales_count">0</strong>
                    </div>
                    <div class="arching-summary-card">
                        <small>Total vendido</small>
                        <strong id="detail_sales_total">S/ 0.00</strong>
                    </div>
                    <div class="arching-summary-card">
                        <small>Total bruto</small>
                        <strong id="detail_gross_total">S/ 0.00</strong>
                    </div>
                    <div class="arching-summary-card">
                        <small>Anulados</small>
                        <strong id="detail_annulled_total">S/ 0.00</strong>
                    </div>
                    <div class="arching-summary-card">
                        <small>Comprobantes anulados</small>
                        <strong id="detail_annulled_count">0</strong>
                    </div>
                    <div class="arching-summary-card">
                        <small>Egresos</small>
                        <strong id="detail_expenses_total">S/ 0.00</strong>
                    </div>
                    <div class="arching-summary-card">
                        <small>Egresos registrados</small>
                        <strong id="detail_expenses_count">0</strong>
                    </div>
                    <div class="arching-summary-card is-final">
                        <small>Monto final esperado</small>
                        <strong id="detail_final_amount">S/ 0.00</strong>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="card border shadow-none h-100">
                            <div class="card-header bg-light py-3">
                                <h6 class="mb-0">Medios de pago</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>Metodo</th>
                                                <th class="text-end">Total</th>
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
                            <div class="card-header bg-light py-3">
                                <h6 class="mb-0">Movimientos de la caja</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="archingMovementsTable" class="table table-hover table-sm mb-0">
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
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-dark" id="btn-print-summary">
                    <i class="ri-printer-line me-1"></i>Ticket
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
