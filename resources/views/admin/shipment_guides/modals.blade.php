<div class="modal fade" id="modalDetailShipmentGuide" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de guia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><strong>Guia:</strong> <span id="detail_guide_document"></span></div>
                    <div class="col-md-4"><strong>Emision:</strong> <span id="detail_guide_date"></span></div>
                    <div class="col-md-4"><strong>Inicio:</strong> <span id="detail_guide_transfer_date"></span></div>
                    <div class="col-md-6"><strong>Cliente:</strong> <span id="detail_guide_client"></span></div>
                    <div class="col-md-6"><strong>Modo:</strong> <span id="detail_guide_transport_mode"></span></div>
                    <div class="col-md-6"><strong>Partida:</strong> <span id="detail_guide_origin"></span></div>
                    <div class="col-md-6"><strong>Llegada:</strong> <span id="detail_guide_destination"></span></div>
                    <div class="col-md-6"><strong>Placa principal:</strong> <span id="detail_guide_main_plate"></span></div>
                    <div class="col-md-6"><strong>Placa remolque / carreta:</strong> <span id="detail_guide_secondary_plate"></span></div>
                    <div class="col-md-6"><strong>Conductor:</strong> <span id="detail_guide_driver"></span></div>
                    <div class="col-md-6"><strong>Transportista:</strong> <span id="detail_guide_carrier"></span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Descripcion</th>
                                <th>Codigo</th>
                                <th>Unidad</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody id="detail_guide_items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
