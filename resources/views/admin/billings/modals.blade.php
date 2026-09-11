<div class="modal fade" id="modalCreditNoteBilling" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Anular con nota de credito</h5>
                    <p class="text-muted mb-0" id="creditNoteBillingLabel">Selecciona el motivo de anulacion.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formCreditNoteBilling">
                    @csrf
                    <input type="hidden" id="credit_note_billing_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motivo SUNAT</label>
                        <select class="form-select" id="credit_note_type_id">
                            <option value="">Seleccionar...</option>
                            @foreach ($creditNoteTypes as $creditNoteType)
                                <option value="{{ $creditNoteType->id }}">
                                    {{ $creditNoteType->codigo }} - {{ $creditNoteType->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Esta etapa esta enfocada en anulacion total del comprobante.</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Descripcion / motivo interno</label>
                        <textarea class="form-control" id="credit_note_reason" rows="3" placeholder="Ejemplo: Anulacion de la operacion por error en la emision."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnSaveCreditNoteBilling">
                    <span class="credit-note-save-text">Emitir nota de credito</span>
                    <span class="credit-note-save-loading d-none">Anulando...</span>
                    <span class="spinner-border spinner-border-sm d-none credit-note-save-spinner"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDebitNoteBilling" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Emitir nota de debito</h5>
                    <p class="text-muted mb-0" id="debitNoteBillingLabel">Selecciona el motivo del ajuste.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formDebitNoteBilling">
                    @csrf
                    <input type="hidden" id="debit_note_billing_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motivo SUNAT</label>
                        <select class="form-select" id="debit_note_type_id">
                            <option value="">Seleccionar...</option>
                            @foreach ($debitNoteTypes as $debitNoteType)
                                <option value="{{ $debitNoteType->id }}">
                                    {{ $debitNoteType->codigo }} - {{ $debitNoteType->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Descripcion / motivo interno</label>
                        <textarea class="form-control" id="debit_note_reason" rows="3" placeholder="Ejemplo: Ajuste por interes por mora o penalidad."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSaveDebitNoteBilling">
                    <span class="debit-note-save-text">Emitir nota de debito</span>
                    <span class="debit-note-save-loading d-none">Emitiendo...</span>
                    <span class="spinner-border spinner-border-sm d-none debit-note-save-spinner"></span>
                </button>
            </div>
        </div>
    </div>
</div>
