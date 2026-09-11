<!-- Modal: Registrar Devolución Directa de Envases -->
<div class="modal fade" id="modal-direct-return" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="ri-arrow-go-back-line me-1 text-primary"></i> Registrar Devolución de Envases
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-direct-return">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cliente <span class="text-danger">*</span></label>
                        <select name="idcliente" id="return_idcliente" class="form-select" required>
                            <option value="">[Seleccionar Cliente]</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" data-balance="{{ $c->saldo_envases }}">
                                    {{ $c->nombres }} (Saldo actual: {{ $c->saldo_envases }} envases)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-success">
                                <i class="ri-checkbox-circle-line me-1"></i> Devueltos Intactos (Aptos)
                            </label>
                            <input type="number" name="devueltos_intactos" id="return_intact" class="form-control" min="0" value="0" required>
                            <small class="text-muted" style="font-size: 11px;">Listos para lavado y recarga.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-danger">
                                <i class="ri-error-warning-line me-1"></i> Devueltos Dañados (Rotos)
                            </label>
                            <input type="number" name="devueltos_danados" id="return_damaged" class="form-control" min="0" value="0" required>
                            <small class="text-muted" style="font-size: 11px;">Rajados, fisurados o mermas.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cobro por Reposición (S/)</label>
                            <input type="number" step="0.50" name="costo_dano" id="return_cost" class="form-control" min="0" value="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Observación</label>
                            <input type="text" name="observaciones" class="form-control" placeholder="Entregado en planta, etc.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ri-save-line me-1"></i> Registrar Devolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Ajuste Manual de Saldo de Envases -->
<div class="modal fade" id="modal-adjust-balance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="ri-equalizer-line me-1 text-warning"></i> Ajuste Manual de Saldo de Envases
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-adjust-balance">
                @csrf
                <input type="hidden" name="idcliente" id="adjust_idcliente">
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="small text-muted">Cliente: <strong id="adjust_client_name"></strong></div>
                        <div class="small text-muted">Saldo registrado actual: <strong id="adjust_current_balance" class="text-primary">0</strong> envases</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nuevo Saldo Real de Envases <span class="text-danger">*</span></label>
                        <input type="number" name="nuevo_saldo" id="adjust_new_balance" class="form-control" min="0" required>
                        <small class="text-muted">Ingresa la cantidad exacta de envases que el cliente tiene en su poder.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Motivo del Ajuste <span class="text-danger">*</span></label>
                        <input type="text" name="motivo" class="form-control" required placeholder="Ej. Conteo físico en local, regularización de comodato">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark px-4">
                        <i class="ri-save-line me-1"></i> Guardar Ajuste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Historial de Envases del Cliente -->
<div class="modal fade" id="modal-client-history" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="ri-history-line me-1 text-primary"></i> Historial de Envases: <span id="history_client_name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Llenos Entregados</th>
                                <th>Devueltos Intactos</th>
                                <th>Devueltos Dañados</th>
                                <th>Saldo Resultante</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody id="history_movements_body">
                            <!-- Dinámico -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
