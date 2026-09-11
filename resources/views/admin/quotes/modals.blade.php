<div class="modal fade" id="modalQuoteDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="small text-muted">Cotización</div>
                        <div class="fw-semibold" id="quote-detail-document">-</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Fecha</div>
                        <div class="fw-semibold" id="quote-detail-date">-</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Cliente</div>
                        <div class="fw-semibold" id="quote-detail-customer">-</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Total</div>
                        <div class="fw-semibold" id="quote-detail-total">-</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Und.</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">P. Unitario</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody id="quote-detail-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
