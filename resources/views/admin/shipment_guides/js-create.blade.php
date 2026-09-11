<script>
    const guideItems = [];

    function renderGuideItems() {
        let rows = '';

        guideItems.forEach((item, index) => {
            rows += `<tr>
                <td>${item.descripcion}</td>
                <td>${item.codigo || '-'}</td>
                <td>${item.unidad}</td>
                <td>${Number(item.cantidad).toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-guide-item" data-index="${index}">Quitar</button></td>
            </tr>`;
        });

        $('#guide_items_table tbody').html(rows || '<tr><td colspan="5" class="text-center text-muted">Aun no agregas productos.</td></tr>');
    }

    $(function () {
        $('#guide_client_id, #guide_product_select').select2({ width: '100%' });

        $('.guide-ubigeo-select').select2({
            width: '100%',
            ajax: {
                url: "{{ route('admin.search_shipment_guide_ubigeo') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    return data;
                }
            },
            placeholder: 'Buscar ubigeo...',
            minimumInputLength: 2
        });

        $('#motivo_traslado_codigo').on('change', function () {
            $('#motivo_traslado_descripcion').val($(this).find(':selected').data('description') || '');
        });

        function clearTransportFields(selector) {
            $(selector).find('input').val('');
            $(selector).find('select').val('').trigger('change.select2');
        }

        function toggleTransportFields() {
            const transportMode = $('select[name="modo_transporte"]').val();
            const isPrivate = transportMode === '02';

            $('.transport-private-field').toggleClass('d-none', !isPrivate);
            $('.transport-public-field').toggleClass('d-none', isPrivate);

            if (isPrivate) {
                clearTransportFields('.transport-public-field');
            } else {
                clearTransportFields('.transport-private-field');
            }
        }

        $('select[name="modo_transporte"]').on('change', toggleTransportFields);
        toggleTransportFields();

        $('#guide_client_id').on('change', function () {
            const selected = $(this).find(':selected');
            const address = selected.data('address') || '';
            const ubigeo = selected.data('ubigeo') || '';

            $('input[name="llegada_direccion"]').val(address);

            if (ubigeo) {
                const text = selected.text();
                const option = new Option(text, ubigeo, true, true);
                $('#llegada_ubigeo').append(option).trigger('change');
            }
        });

        $('#btnAddGuideItem').on('click', function () {
            const selected = $('#guide_product_select').find(':selected');
            const productId = $('#guide_product_select').val();
            const quantity = parseFloat($('#guide_product_qty').val() || '0');

            if (!productId) {
                toast_msg('Selecciona un producto.', 'warning');
                return;
            }

            if (!quantity || quantity <= 0) {
                toast_msg('Ingresa una cantidad valida.', 'warning');
                return;
            }

            guideItems.push({
                product_id: productId,
                codigo: selected.data('code') || '',
                descripcion: selected.data('description') || selected.text(),
                unidad: 'NIU',
                cantidad: quantity
            });

            renderGuideItems();
            $('#guide_product_select').val('').trigger('change');
            $('#guide_product_qty').val('1.00');
        });

        $('body').on('click', '.btn-remove-guide-item', function () {
            guideItems.splice(Number($(this).data('index')), 1);
            renderGuideItems();
        });

        $('#btnSaveShipmentGuide').on('click', function () {
            const payload = {
                _token: "{{ csrf_token() }}",
                fecha_emision: $('input[name="fecha_emision"]').val(),
                fecha_inicio_traslado: $('input[name="fecha_inicio_traslado"]').val(),
                motivo_traslado_codigo: $('select[name="motivo_traslado_codigo"]').val(),
                motivo_traslado_descripcion: $('#motivo_traslado_descripcion').val(),
                modo_transporte: $('select[name="modo_transporte"]').val(),
                idcliente: $('select[name="idcliente"]').val(),
                peso_total: $('input[name="peso_total"]').val(),
                unidad_peso: $('input[name="unidad_peso"]').val(),
                partida_ubigeo: $('select[name="partida_ubigeo"]').val(),
                partida_direccion: $('input[name="partida_direccion"]').val(),
                llegada_ubigeo: $('select[name="llegada_ubigeo"]').val(),
                llegada_direccion: $('input[name="llegada_direccion"]').val(),
                transportista_documento_tipo: '6',
                transportista_documento: $('input[name="transportista_documento"]').val(),
                transportista_nombre: $('input[name="transportista_nombre"]').val(),
                conductor_documento_tipo: '1',
                conductor_documento: $('input[name="conductor_documento"]').val(),
                conductor_nombre: $('input[name="conductor_nombre"]').val(),
                placa_vehiculo: $('input[name="placa_vehiculo"]').val(),
                placa_secundaria: $('input[name="placa_secundaria"]').val(),
                observaciones: $('input[name="observaciones"]').val(),
                items: guideItems
            };

            $.ajax({
                url: "{{ route('admin.save_shipment_guide') }}",
                method: 'POST',
                data: payload,
                beforeSend: function () {
                    block_content('#layout-content');
                    $('#btnSaveShipmentGuide').prop('disabled', true);
                },
                success: function (r) {
                    close_block('#layout-content');
                    $('#btnSaveShipmentGuide').prop('disabled', false);
                    toast_msg(r.msg, r.type || 'success');
                    if (r.redirect) {
                        window.location.href = r.redirect;
                    }
                },
                error: function (xhr) {
                    close_block('#layout-content');
                    $('#btnSaveShipmentGuide').prop('disabled', false);
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo registrar la guia.', xhr.responseJSON?.type || 'warning');
                },
                dataType: 'json'
            });
        });

        renderGuideItems();
    });
</script>
