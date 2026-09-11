<!-- Modal: Nuevo Pedido de Entrega -->
<div class="modal fade" id="modal-create-order" tabindex="-1" aria-labelledby="modalCreateOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="modalCreateOrderLabel">
                    <i class="ri-add-circle-line me-1 text-primary"></i> Registrar Nuevo Pedido de Reparto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-create-order">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cliente <span class="text-danger">*</span></label>
                            <select id="modal_idcliente" name="idcliente" class="form-select select2-modal" required>
                                <option value="">[Seleccione Cliente]</option>
                                @foreach($clients as $cli)
                                    <option value="{{ $cli->id }}" 
                                        data-phone="{{ $cli->telefono }}" 
                                        data-address="{{ $cli->direccion }}"
                                        data-balance="{{ $cli->saldo_envases }}">
                                        {{ $cli->nombres }} ({{ $cli->nro_documento }}) - Saldo: {{ $cli->saldo_envases }} envases
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Fecha de Entrega <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_programada" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Franja Horaria</label>
                            <select name="franja_horaria" class="form-select">
                                <option value="flexible">Flexible / Durante el día</option>
                                <option value="manana">Mañana (08:00 - 13:00)</option>
                                <option value="tarde">Tarde (14:00 - 18:00)</option>
                                <option value="noche">Noche (18:00 - 21:00)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Dirección de Entrega <span class="text-danger">*</span></label>
                            <input type="text" id="modal_direccion" name="direccion_entrega" class="form-control" required placeholder="Av. / Jr. / Calle y Nro.">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Referencia</label>
                            <input type="text" id="modal_referencia" name="referencia" class="form-control" placeholder="Frente al parque...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Teléfono Contacto</label>
                            <input type="text" id="modal_telefono" name="telefono_contacto" class="form-control" placeholder="999 999 999">
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-dark"><i class="ri-shopping-basket-line me-1 text-primary"></i> Productos del Pedido</span>
                                <button type="button" class="btn btn-outline-primary btn-sm btn-add-order-item">
                                    <i class="ri-add-line"></i> Agregar producto
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="table-modal-items">
                                    <thead>
                                        <tr>
                                            <th width="45%">Producto</th>
                                            <th width="20%">Cantidad</th>
                                            <th width="20%">Precio Unit.</th>
                                            <th width="15%">Subtotal</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-items-body">
                                        <!-- Se agregan dinámicamente -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end fw-bold">Total a Cobrar:</th>
                                            <th colspan="2" class="fw-bold text-primary" id="modal-total-display">S/ 0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Método de Pago Preferido</label>
                            <select name="metodo_pago" class="form-select">
                                <option value="contraentrega">Efectivo contraentrega</option>
                                <option value="yape">Yape</option>
                                <option value="plin">Plin</option>
                                <option value="transferencia">Transferencia bancaria</option>
                                <option value="credito">Crédito / Cuenta Corriente</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Notas o Indicaciones</label>
                            <input type="text" name="notas" class="form-control" placeholder="Tocar timbre blanco, dejar en portería, etc.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 btn-submit-create-order">
                        <i class="ri-save-line me-1"></i> Guardar y Crear Pedido
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Asignar Repartidor -->
<div class="modal fade" id="modal-assign-driver" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="ri-truck-line me-1 text-primary"></i> Asignar Repartidor y Despachar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-assign-driver">
                @csrf
                <input type="hidden" name="id" id="assign_order_id">
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Selecciona el repartidor encargado de la ruta para el pedido <strong id="assign_order_code"></strong>. El estado del pedido cambiará a <strong>En Ruta</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Repartidor Responsable <span class="text-danger">*</span></label>
                        <select name="idrepartidor" id="assign_driver_id" class="form-select" required>
                            <option value="">[Seleccionar repartidor]</option>
                            @foreach($repartidores as $rep)
                                <option value="{{ $rep->id }}">{{ $rep->nombres }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-send-plane-line me-1"></i> Despachar a Ruta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Completar Entrega y Liquidar Envases -->
<div class="modal fade" id="modal-complete-delivery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="ri-check-double-line me-1 text-success"></i> Completar Entrega y Retorno de Envases
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-complete-delivery">
                @csrf
                <input type="hidden" name="id" id="complete_order_id">
                <div class="modal-body">
                    <div class="alert alert-light border mb-3 p-3">
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">Orden: <strong id="complete_order_code"></strong></span>
                            <span class="small text-muted">Cliente: <strong id="complete_order_client"></strong></span>
                        </div>
                        <div class="mt-2 fw-bold text-primary">
                            Bidones llenos entregados: <span id="complete_delivered_count" class="badge bg-primary">0</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-success">
                                <i class="ri-checkbox-circle-line me-1"></i> Vacíos Intactos Recibidos <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="bidones_vacios_recibidos" id="complete_intact" class="form-control" min="0" value="0" required>
                            <small class="text-muted" style="font-size: 11px;">Envases aptos para lavado y recarga.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-danger">
                                <i class="ri-error-warning-line me-1"></i> Vacíos Dañados / Rotos
                            </label>
                            <input type="number" name="bidones_danados_recibidos" id="complete_damaged" class="form-control" min="0" value="0">
                            <small class="text-muted" style="font-size: 11px;">Fisurados, rotos o inservibles.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cobro por Envases Dañados (S/)</label>
                            <input type="number" step="0.50" name="cobro_envases_danados" id="complete_damage_cost" class="form-control" min="0" value="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Método de Pago Recibido</label>
                            <select name="metodo_pago" id="complete_payment_method" class="form-select">
                                <option value="efectivo">Efectivo</option>
                                <option value="yape">Yape</option>
                                <option value="plin">Plin</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="credito">Queda a crédito</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="complete_paid_switch" name="estado_pago" value="pagado" checked>
                                <label class="form-check-label small fw-bold" for="complete_paid_switch">Marcar pedido como PAGADO</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="ri-check-line me-1"></i> Confirmar y Liquidar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Ver Detalles del Pedido -->
<div class="modal fade" id="modal-view-order" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="view_order_title">Detalle del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="view_order_content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
