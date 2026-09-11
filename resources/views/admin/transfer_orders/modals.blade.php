<style>
    #modalConfirmTransfer .modal-content {
        border: 1px solid rgba(33, 40, 50, 0.08);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.16);
    }

    #modalConfirmTransfer .modal-header {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        padding: 1.1rem 1.35rem;
    }

    .transfer-modal-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-weight: 800;
        color: #1f2937;
    }

    .transfer-modal-title-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(37, 99, 235, .12), rgba(16, 185, 129, .16));
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, .12);
        font-size: 1.1rem;
    }

    .transfer-order-banner {
        background: linear-gradient(135deg, rgba(0, 97, 242, .08), rgba(0, 172, 105, .08));
        border: 1px solid rgba(37, 99, 235, .14);
        border-radius: 18px;
        padding: 1rem 1.1rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .05);
    }

    .transfer-order-banner-label {
        font-size: .76rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: .25rem;
        font-weight: 700;
    }

    .transfer-order-banner-number {
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
    }

    .transfer-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .9rem;
        margin-top: 1rem;
    }

    .transfer-info-card {
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 18px;
        padding: .95rem 1rem;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .04);
    }

    .transfer-info-card.full {
        grid-column: 1 / -1;
    }

    .transfer-info-label {
        font-size: .74rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .transfer-info-value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.35;
    }

    .transfer-items-shell {
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .04);
    }

    .transfer-items-head {
        padding: .95rem 1rem;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid rgba(148, 163, 184, .16);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .transfer-items-title {
        font-weight: 800;
        color: #1f2937;
    }

    .transfer-items-subtitle {
        font-size: .85rem;
        color: #64748b;
    }

    #modalConfirmTransfer table {
        margin-bottom: 0;
    }

    #modalConfirmTransfer thead th {
        background: #f8fafc;
        color: #475569;
        font-size: .78rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(148, 163, 184, .16);
        padding: .85rem .9rem;
    }

    #modalConfirmTransfer tbody td {
        padding: .9rem;
        vertical-align: middle;
    }

    #modalConfirmTransfer tbody tr:hover td {
        background: #f8fafc;
    }

    #modalConfirmTransfer .modal-footer {
        border-top: 1px solid rgba(148, 163, 184, .16);
        background: #fff;
        padding: 1rem 1.25rem 1.2rem;
    }

    #modalConfirmTransfer .btn {
        min-width: 128px;
        border-radius: 14px;
        min-height: 46px;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .transfer-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="modal fade" id="modalConfirmTransfer" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <form id="form_save" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title transfer-modal-title mb-0">
                    <span class="transfer-modal-title-icon"><i class="ri-inbox-archive-line"></i></span>
                    <span>Traslado de Productos</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-12">
                        <input type="hidden" name="idtransfer">

                        <div class="transfer-order-banner">
                            <div class="transfer-order-banner-label">Orden de traslado</div>
                            <div class="transfer-order-banner-number"><span class="nro__orden"></span></div>
                        </div>

                        <div class="transfer-info-grid">
                            <div class="transfer-info-card">
                                <div class="transfer-info-label">Fecha de emisi&oacute;n</div>
                                <div class="transfer-info-value td__emision"></div>
                            </div>
                            <div class="transfer-info-card">
                                <div class="transfer-info-label">Fecha de vencimiento</div>
                                <div class="transfer-info-value td__expiration"></div>
                            </div>
                            <div class="transfer-info-card">
                                <div class="transfer-info-label">Almac&eacute;n despacho</div>
                                <div class="transfer-info-value td__dispatch"></div>
                            </div>
                            <div class="transfer-info-card">
                                <div class="transfer-info-label">Almac&eacute;n destino</div>
                                <div class="transfer-info-value td__receiver"></div>
                            </div>
                            <div class="transfer-info-card full">
                                <div class="transfer-info-label">Informaci&oacute;n adicional</div>
                                <div class="transfer-info-value td__optional"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="transfer-items-shell">
                            <div class="transfer-items-head">
                                <div>
                                    <div class="transfer-items-title">Productos incluidos</div>
                                    <div class="transfer-items-subtitle">Detalle del traslado solicitado</div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-start">C&oacute;digo</th>
                                            <th class="text-start">Producto</th>
                                            <th class="text-center">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_detail_transfer"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <button class="btn btn-success btn-save">
                    <span class="text-save">Trasladar</span>
                    <span class="me-1 d-none text-saving" role="status">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span class="text-saving d-none">Trasladando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
