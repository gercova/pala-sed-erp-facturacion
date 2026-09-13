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

    $(document).ready(function() {
        initDeliveriesTable();

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

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

        // Abrir modal completar entrega
        $(document).on('click', '.btn-complete-delivery', function() {
            let id = $(this).data('id');
            let code = $(this).data('code');
            let client = $(this).data('client');
            let delivered = $(this).data('delivered');

            $('#complete_order_id').val(id);
            $('#complete_order_code').text(code);
            $('#complete_order_client').text(client);
            $('#complete_delivered_count').text(delivered);
            $('#complete_intact').val(delivered); // Por defecto sugiere intercambio 1 a 1
            $('#complete_damaged').val(0);
            $('#complete_damage_cost').val('0.00');

            $('#modal-complete-delivery').modal('show');
        });

        // Completar entrega y liquidar envases
        $('#form-complete-delivery').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('deliveries.complete') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(r) {
                    $('#modal-complete-delivery').modal('hide');
                    toast_msg(r.msg, 'success');
                    tableDeliveries.ajax.reload();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'Error al completar entrega', 'error');
                }
            });
        });

        // Cancelar pedido
        $(document).on('click', '.btn-cancel-order', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: '¿Cancelar este pedido?',
                text: 'El estado pasará a cancelado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6e7881',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('deliveries.cancel') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", id: id },
                        success: function(r) {
                            toast_msg(r.msg, 'success');
                            tableDeliveries.ajax.reload();
                        },
                        error: function(xhr) {
                            toast_msg(xhr.responseJSON?.msg || 'Error al cancelar', 'error');
                        }
                    });
                }
            });
        });

        // Ver detalle del pedido
        $(document).on('click', '.btn-order-details', function() {
            let id = $(this).data('id');
            $('#modal-view-order').modal('show');
            $('#view_order_content').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

            $.ajax({
                url: "{{ url('deliveries/show') }}/" + id,
                method: "GET",
                success: function(r) {
                    let o = r.order;
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

                    let html = `
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary mb-1">${o.codigo_orden} - ${o.cliente ? o.cliente.nombres : ''}</h6>
                            <div class="small text-muted"><i class="ri-map-pin-line"></i> ${o.direccion_entrega} ${o.referencia ? '(' + o.referencia + ')' : ''}</div>
                            <div class="small text-muted"><i class="ri-phone-line"></i> ${o.telefono_contacto || '-'}</div>
                        </div>
                        <table class="table table-sm border">
                            <thead class="table-light">
                                <tr><th>Item</th><th class="text-center">Cant</th><th class="text-end">P. Unit</th><th class="text-end">Subtotal</th></tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                            <tfoot>
                                <tr><th colspan="3" class="text-end">Total:</th><th class="text-end text-primary">S/ ${parseFloat(o.total).toFixed(2)}</th></tr>
                            </tfoot>
                        </table>
                        <div class="p-2 bg-light rounded small">
                            <div><strong>Método de pago:</strong> ${o.metodo_pago} (${o.estado_pago})</div>
                            <div><strong>Origen:</strong> ${o.origen.toUpperCase()}</div>
                            ${o.notas ? '<div><strong>Notas:</strong> ' + o.notas + '</div>' : ''}
                        </div>
                    `;
                    $('#view_order_content').html(html);
                }
            });
        });

    });
</script>
