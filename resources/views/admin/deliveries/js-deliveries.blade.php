<style>
    @keyframes spin-anim {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spin-animation {
        display: inline-block;
        animation: spin-anim 0.75s linear infinite;
    }
    .dropdown-menu .dropdown-item {
        transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
    }
    .dropdown-menu .dropdown-item:hover {
        background-color: rgba(0, 97, 242, 0.08);
    }
    .dropdown-menu .dropdown-item.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.08);
        color: #dc3545 !important;
    }
</style>
<script>
    let tableDeliveries = null;
    const availableProducts = @json($products);

    function initDeliveriesTable() {
        tableDeliveries = $('#table-deliveries').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('deliveries.get') }}",
                data: function(d) {
                    d.estado = $('#filter_estado').val();
                    d.fecha = $('#filter_fecha').val();
                    d.idrepartidor = $('#filter_repartidor').val();
                }
            },
            columns: [
                { data: 'codigo_orden', name: 'codigo_orden', className: 'fw-bold text-dark' },
                { data: 'fecha_programada', name: 'fecha_programada' },
                { data: 'cliente', name: 'cliente' },
                { data: 'direccion_entrega', name: 'direccion_entrega' },
                { data: 'envases_badge', name: 'envases_badge', orderable: false, searchable: false },
                { data: 'repartidor', name: 'repartidor' },
                { data: 'origen', name: 'origen', className: 'text-center' },
                { data: 'estado', name: 'estado', className: 'text-center' },
                { data: 'total', name: 'total', className: 'text-end' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    }

    function addModalItemRow(productId = '', qty = 1) {
        let options = '<option value="">[Seleccionar Producto]</option>';
        availableProducts.forEach(p => {
            let selected = String(p.id) === String(productId) ? 'selected' : '';
            options += `<option value="${p.id}" data-price="${p.precio_venta}" ${selected}>${p.descripcion} - S/ ${parseFloat(p.precio_venta).toFixed(2)}</option>`;
        });

        let row = `
            <tr class="item-row">
                <td>
                    <select class="form-select form-select-sm product-select" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-select-sm item-qty" min="1" step="1" value="${qty}" required>
                </td>
                <td>
                    <input type="number" class="form-control form-select-sm item-price" step="0.50" value="0.00" required>
                </td>
                <td class="item-subtotal fw-bold text-dark text-end">S/ 0.00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item p-1"><i class="ri-delete-bin-line"></i></button>
                </td>
            </tr>
        `;

        $('#modal-items-body').append(row);

        if (productId) {
            let selectedOpt = $(`#modal-items-body tr:last .product-select option:selected`);
            let price = selectedOpt.data('price') || 0;
            $(`#modal-items-body tr:last .item-price`).val(parseFloat(price).toFixed(2));
        }

        calcModalTotal();
    }

    function calcModalTotal() {
        let total = 0;
        $('#modal-items-body tr').each(function() {
            let qty = parseFloat($(this).find('.item-qty').val()) || 0;
            let price = parseFloat($(this).find('.item-price').val()) || 0;
            let subtotal = qty * price;
            $(this).find('.item-subtotal').text('S/ ' + subtotal.toFixed(2));
            total += subtotal;
        });

        $('#modal-total-display').text('S/ ' + total.toFixed(2));
    }

    let pollingInterval = null;
    let isPollingActive = true;

    function isUserInteracting() {
        return $('.modal.show').length > 0 || $('input:focus, select:focus, textarea:focus').length > 0;
    }

    function startPolling() {
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(function() {
            if (isPollingActive && tableDeliveries && !isUserInteracting()) {
                tableDeliveries.ajax.reload(null, false);
            }
        }, 15000);
    }

    function togglePolling() {
        isPollingActive = !isPollingActive;
        if (isPollingActive) {
            $('#live-polling-badge').removeClass('bg-secondary-soft text-secondary').addClass('bg-success-soft text-success');
            $('#live-polling-badge .spinner-grow').show();
            $('#polling-status-text').text('En vivo (15s)');
            $('#polling-toggle-icon').removeClass('ri-play-line').addClass('ri-pause-line');
            $('#btn-toggle-polling').attr('title', 'Pausar actualización automática');
            startPolling();
        } else {
            $('#live-polling-badge').removeClass('bg-success-soft text-success').addClass('bg-secondary-soft text-secondary');
            $('#live-polling-badge .spinner-grow').hide();
            $('#polling-status-text').text('Pausado');
            $('#polling-toggle-icon').removeClass('ri-pause-line').addClass('ri-play-line');
            $('#btn-toggle-polling').attr('title', 'Reanudar actualización automática');
        }
    }

    $(document).ready(function() {
        initDeliveriesTable();

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Sincronizar contadores KPI cuando la tabla responde
        $('#table-deliveries').on('xhr.dt', function(e, settings, json, xhr) {
            if (json && json.kpis) {
                $('#kpi-pendientes').text(json.kpis.pendientes);
                $('#kpi-en-ruta').text(json.kpis.en_ruta);
                $('#kpi-entregados').text(json.kpis.entregados_hoy);
                $('#kpi-recaudado').text('S/ ' + parseFloat(json.kpis.recaudado_hoy).toFixed(2));
            }
        });

        // Botón Refrescar Tabla Manualmente
        $('#btn-refresh-table').on('click', function() {
            let $btn = $(this);
            let $icon = $btn.find('i');
            $btn.prop('disabled', true);
            $icon.addClass('spin-animation');
            tableDeliveries.ajax.reload(function() {
                $btn.prop('disabled', false);
                $icon.removeClass('spin-animation');
                if (typeof toast_msg === 'function') {
                    toast_msg('Bandeja de pedidos actualizada', 'info');
                }
            }, false);
        });

        // Filtros
        $('#btn-filter').on('click', function() {
            tableDeliveries.ajax.reload();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter_estado').val('');
            $('#filter_repartidor').val('');
            $('#filter_fecha').val('');
            tableDeliveries.ajax.reload();
        });

        // Toggle auto-polling (15s)
        $('#btn-toggle-polling').on('click', function() {
            togglePolling();
        });

        startPolling();

        // Abrir modal de nuevo pedido
        $('.btn-create-order').on('click', function() {
            $('#form-create-order')[0].reset();
            $('#modal-items-body').empty();

            // Agregar un producto inicial por defecto (recarga)
            let defaultProduct = availableProducts.find(p => p.descripcion.toLowerCase().includes('recarga')) || availableProducts[0];
            if (defaultProduct) {
                addModalItemRow(defaultProduct.id, 1);
            }

            $('#modal-create-order').modal('show');
        });

        // Autocompletar datos al seleccionar cliente
        $('#modal_idcliente').on('change', function() {
            let opt = $(this).find('option:selected');
            $('#modal_telefono').val(opt.data('phone') || '');
            $('#modal_direccion').val(opt.data('address') || '');
        });

        // Agregar fila de producto en modal
        $('.btn-add-order-item').on('click', function() {
            addModalItemRow();
        });

        // Cambio de producto en fila
        $(document).on('change', '.product-select', function() {
            let price = $(this).find('option:selected').data('price') || 0;
            $(this).closest('tr').find('.item-price').val(parseFloat(price).toFixed(2));
            calcModalTotal();
        });

        // Cambio de cantidad o precio
        $(document).on('input', '.item-qty, .item-price', function() {
            calcModalTotal();
        });

        // Eliminar fila
        $(document).on('click', '.btn-remove-item', function() {
            if ($('#modal-items-body tr').length > 1) {
                $(this).closest('tr').remove();
                calcModalTotal();
            } else {
                toast_msg('El pedido debe tener al menos un producto.', 'warning');
            }
        });

        // Guardar nuevo pedido manual
        $('#form-create-order').on('submit', function(e) {
            e.preventDefault();

            let items = [];
            $('#modal-items-body tr').each(function() {
                let id = $(this).find('.product-select').val();
                let qty = $(this).find('.item-qty').val();
                let price = $(this).find('.item-price').val();
                if (id) {
                    items.push({ idproducto: id, cantidad: qty, precio_unitario: price });
                }
            });

            if (items.length === 0) {
                toast_msg('Debes agregar al menos un producto al pedido.', 'warning');
                return;
            }

            let data = $(this).serializeArray();
            items.forEach((item, index) => {
                data.push({ name: `items[${index}][idproducto]`, value: item.idproducto });
                data.push({ name: `items[${index}][cantidad]`, value: item.cantidad });
                data.push({ name: `items[${index}][precio_unitario]`, value: item.precio_unitario });
            });

            $.ajax({
                url: "{{ route('deliveries.store') }}",
                method: "POST",
                data: $.param(data),
                success: function(r) {
                    $('#modal-create-order').modal('hide');
                    toast_msg(r.msg, 'success');
                    tableDeliveries.ajax.reload();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'Error al registrar pedido', 'error');
                }
            });
        });

        // Abrir modal despachar a ruta
        $(document).on('click', '.btn-assign-driver', function() {
            let id = $(this).data('id');
            let code = $(this).data('code');
            $('#assign_order_id').val(id);
            $('#assign_order_code').text(code);
            $('#modal-assign-driver').modal('show');
        });

        // Despachar a ruta
        $('#form-assign-driver').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('deliveries.assign') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(r) {
                    $('#modal-assign-driver').modal('hide');
                    toast_msg(r.msg, 'success');
                    tableDeliveries.ajax.reload();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'Error al despachar pedido', 'error');
                }
            });
        });

        // Abrir modal completar entrega y liquidar
        $(document).on('click', '.btn-complete-delivery', function() {
            let id = $(this).data('id');
            let code = $(this).data('code');
            let client = $(this).data('client');
            let delivered = $(this).data('delivered');
            let subtotal = parseFloat($(this).data('subtotal') || $(this).data('total') || 0);
            let method = $(this).data('method') || 'efectivo';

            $('#complete_order_id').val(id);
            $('#complete_order_code').text(code);
            $('#complete_order_client').text(client);
            $('#complete_delivered_count').text(delivered);
            $('#complete_intact').val(delivered); // Por defecto intercambio 1 a 1
            $('#complete_damaged').val(0);
            $('#complete_damage_cost').val('0.00');
            $('#complete_base_subtotal').val(subtotal);
            $('#complete_order_subtotal').text('S/ ' + subtotal.toFixed(2));
            $('#complete_display_damage_cost').text('S/ 0.00');
            $('#complete_calculated_total').text('S/ ' + subtotal.toFixed(2));
            $('#complete_motivo').val('despacho_estandar');
            $('#complete_notas').val('');

            // Preseleccionar método de pago si coincide
            if (method) {
                let normalized = method.toLowerCase();
                $('#complete_payment_method option').each(function() {
                    if ($(this).val().includes(normalized) || normalized.includes($(this).val())) {
                        $(this).prop('selected', true);
                        return false;
                    }
                });
            }

            let $submitBtn = $('#btn-submit-complete');
            $submitBtn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Confirmar y Liquidar');

            $('#modal-complete-delivery').modal('show');
        });

        // Recalcular total cuando cambie el cobro por envases dañados
        $(document).on('input change', '#complete_damage_cost', function() {
            let base = parseFloat($('#complete_base_subtotal').val()) || 0;
            let damage = parseFloat($(this).val()) || 0;
            if (damage < 0) {
                damage = 0;
                $(this).val('0.00');
            }
            let total = base + damage;
            $('#complete_display_damage_cost').text('S/ ' + damage.toFixed(2));
            $('#complete_calculated_total').text('S/ ' + total.toFixed(2));
        });

        // Completar entrega y liquidar envases (Idempotente + doble click lock)
        $('#form-complete-delivery').on('submit', function(e) {
            e.preventDefault();
            let $btn = $('#btn-submit-complete');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Liquidando...');

            $.ajax({
                url: "{{ route('deliveries.complete') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(r) {
                    $('#modal-complete-delivery').modal('hide');
                    $btn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Confirmar y Liquidar');
                    toast_msg(r.msg, r.already_settled ? 'info' : 'success');
                    tableDeliveries.ajax.reload(null, false);

                    if (r.whatsapp_url) {
                        Swal.fire({
                            title: '¡Liquidación Completada!',
                            html: `El pedido fue liquidado exitosamente.<br><br><strong>¿Deseas enviar el comprobante por WhatsApp al cliente?</strong>`,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#25D366',
                            cancelButtonColor: '#6e7881',
                            confirmButtonText: '<i class="ri-whatsapp-line me-1"></i> Enviar Comprobante por WhatsApp',
                            cancelButtonText: 'Cerrar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(r.whatsapp_url, '_blank', 'noopener,noreferrer');
                            }
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Confirmar y Liquidar');
                    toast_msg(xhr.responseJSON?.msg || 'Error al liquidar entrega', 'error');
                }
            });
        });

        // Cancelar pedido
        $(document).on('click', '.btn-cancel-order', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: '¿Cancelar este pedido?',
                text: 'El estado pasará a cancelado y quedará registrado en la auditoría.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6e7881',
                confirmButtonText: 'Sí, cancelar pedido',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('deliveries.cancel') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", id: id, motivo: 'Cancelado por el operador' },
                        success: function(r) {
                            toast_msg(r.msg, 'success');
                            tableDeliveries.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            toast_msg(xhr.responseJSON?.msg || 'Error al cancelar', 'error');
                        }
                    });
                }
            });
        });

        // Ver detalle del pedido y auditoría
        $(document).on('click', '.btn-order-details', function() {
            let id = $(this).data('id');
            $('#modal-view-order').modal('show');
            $('#view_order_content').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

            $.ajax({
                url: "{{ url('deliveries/show') }}/" + id,
                method: "GET",
                success: function(r) {
                    let o = r.order;
                    let maps = r.maps;
                    let statusLogs = r.status_logs || [];

                    let itemsHtml = '';
                    o.items.forEach(i => {
                        itemsHtml += `
                            <tr>
                                <td>${i.descripcion}</td>
                                <td class="text-center">${parseFloat(i.cantidad)}</td>
                                <td class="text-end">S/ ${parseFloat(i.precio_unitario).toFixed(2)}</td>
                                <td class="text-end">S/ ${parseFloat(i.subtotal).toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    // Botones de mapas según requisitos
                    let mapsButtonsHtml = '';
                    if (maps && maps.has_location) {
                        mapsButtonsHtml = `
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <a href="${maps.view_url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger">
                                    <i class="ri-map-pin-line me-1"></i> Ver Ubicación en Maps
                                </a>
                                <a href="${maps.route_url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-navigation-line me-1"></i> Cómo llegar (Ruta GPS)
                                </a>
                            </div>
                        `;
                    } else {
                        mapsButtonsHtml = `
                            <div class="alert alert-warning py-1 px-2 small mb-0 mt-2 text-dark">
                                <i class="ri-information-line me-1"></i> Sin coordenadas ni dirección registradas para este pedido.
                            </div>
                        `;
                    }

                    // Historial de auditoría de estados
                    let auditRows = '';
                    if (statusLogs.length > 0) {
                        statusLogs.forEach(log => {
                            let userText = log.usuario ? log.usuario.nombres : 'Sistema';
                            let motivoText = log.motivo || log.notas || '-';
                            auditRows += `
                                <tr>
                                    <td class="small">${log.created_at ? log.created_at.substring(0, 16).replace('T', ' ') : '-'}</td>
                                    <td class="small fw-semibold">${userText}</td>
                                    <td><span class="badge bg-light text-dark">${log.estado_anterior || 'inicio'}</span> &rarr; <span class="badge bg-primary-soft text-primary">${log.estado_nuevo}</span></td>
                                    <td class="small text-muted">${motivoText}</td>
                                </tr>
                            `;
                        });
                    } else {
                        auditRows = '<tr><td colspan="4" class="text-center small text-muted">Sin registros de auditoría previos</td></tr>';
                    }

                    let html = `
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold text-primary mb-1">${o.codigo_orden}</h5>
                                    <div class="fw-semibold text-dark">${o.cliente ? o.cliente.nombres : 'Cliente no asignado'}</div>
                                    <div class="small text-muted"><i class="ri-phone-line me-1"></i> Teléfono: ${o.telefono_contacto || '-'}</div>
                                    <div class="small text-muted mt-1"><i class="ri-map-pin-line me-1"></i> Dirección: ${o.direccion_entrega} ${o.referencia ? '(' + o.referencia + ')' : ''}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-info-soft text-info fs-6 text-uppercase">${o.estado}</span>
                                    <div class="small text-muted mt-1">Repartidor: <strong>${o.repartidor ? o.repartidor.nombres : 'Sin asignar'}</strong></div>
                                </div>
                            </div>
                            ${mapsButtonsHtml}
                        </div>

                        <h6 class="fw-bold text-dark small text-uppercase mb-2"><i class="ri-shopping-basket-line me-1 text-primary"></i> Productos del Pedido</h6>
                        <table class="table table-sm border mb-3">
                            <thead class="table-light">
                                <tr><th>Item</th><th class="text-center">Cant</th><th class="text-end">P. Unit</th><th class="text-end">Subtotal</th></tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                            <tfoot>
                                <tr><th colspan="3" class="text-end">Subtotal:</th><th class="text-end">S/ ${parseFloat(o.subtotal || o.total).toFixed(2)}</th></tr>
                                ${parseFloat(o.cobro_envases_danados) > 0 ? `<tr><th colspan="3" class="text-end text-danger">+ Envases Dañados:</th><th class="text-end text-danger">S/ ${parseFloat(o.cobro_envases_danados).toFixed(2)}</th></tr>` : ''}
                                <tr><th colspan="3" class="text-end fw-bold">TOTAL:</th><th class="text-end fw-bold text-success fs-6">S/ ${parseFloat(o.total).toFixed(2)}</th></tr>
                            </tfoot>
                        </table>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-white small">
                                    <div><strong>Envases:</strong> ${o.bidones_a_entregar} llenos entregados</div>
                                    <div><strong>Devueltos:</strong> ${o.bidones_vacios_recibidos} intactos | ${o.bidones_danados_recibidos} dañados</div>
                                    <div><strong>Motivo Liquidación:</strong> ${o.motivo_liquidacion || 'Despacho estándar'}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-white small">
                                    <div><strong>Método de pago:</strong> ${o.metodo_pago ? o.metodo_pago.toUpperCase() : 'EFECTIVO'} (${o.estado_pago})</div>
                                    <div><strong>Arqueo Caja ID:</strong> ${o.idarqueocaja ? '#' + o.idarqueocaja : 'Sin arqueo vinculado'}</div>
                                    <div><strong>Liquidado el:</strong> ${o.liquidado_at || (o.fecha_entrega || 'Pendiente')}</div>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark small text-uppercase mb-2"><i class="ri-history-line me-1 text-primary"></i> Historial y Auditoría de Estados</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr><th>Fecha</th><th>Usuario</th><th>Transición</th><th>Motivo / Notas</th></tr>
                                </thead>
                                <tbody>${auditRows}</tbody>
                            </table>
                        </div>
                    `;
                    $('#view_order_content').html(html);
                },
                error: function(xhr) {
                    $('#view_order_content').html(`<div class="alert alert-danger">${xhr.responseJSON?.msg || 'Error al cargar pedido'}</div>`);
                }
            });
        });

        // Abrir modal de Seguimiento de Envases por Cliente
        let tableContainersSummary = null;
        $('#btn-open-containers-summary').on('click', function() {
            $('#modal-containers-summary').modal('show');
            if (!tableContainersSummary) {
                tableContainersSummary = $('#table-containers-summary').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('deliveries.containers_summary') }}",
                    columns: [
                        { data: 'nombres', name: 'nombres', className: 'fw-semibold text-dark' },
                        { data: 'nro_documento', name: 'nro_documento' },
                        { data: 'telefono', name: 'telefono' },
                        { data: 'direccion', name: 'direccion' },
                        { 
                            data: 'saldo_envases', 
                            name: 'saldo_envases', 
                            className: 'text-center',
                            render: function(data) {
                                let val = parseInt(data) || 0;
                                let badgeClass = val > 0 ? 'bg-primary-soft text-primary' : (val < 0 ? 'bg-danger-soft text-danger' : 'bg-light text-muted');
                                return `<span class="badge ${badgeClass} fs-6 px-2">${val} envases</span>`;
                            }
                        },
                        { data: 'entregados_historicos', orderable: false, searchable: false, className: 'text-center text-success fw-semibold' },
                        { data: 'devueltos_historicos', orderable: false, searchable: false, className: 'text-center text-info fw-semibold' },
                        { data: 'danados_historicos', orderable: false, searchable: false, className: 'text-center text-danger fw-semibold' }
                    ],
                    order: [[4, 'desc']],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                    }
                });
            } else {
                tableContainersSummary.ajax.reload();
            }
        });

    });
</script>
