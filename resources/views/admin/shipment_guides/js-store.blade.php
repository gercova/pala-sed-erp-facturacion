<script>
    $('body').on('click', '.btn-detail-guide', function () {
        const id = $(this).data('id');

        $.post("{{ route('admin.detail_shipment_guide') }}", {
            _token: "{{ csrf_token() }}",
            id: id
        }, function (r) {
            if (!r.status) {
                toast_msg(r.msg, r.type || 'warning');
                return;
            }

            $('#detail_guide_document').text(`${r.data.serie}-${r.data.correlativo}`);
            $('#detail_guide_date').text(r.data.fecha_emision || '-');
            $('#detail_guide_transfer_date').text(r.data.fecha_inicio_traslado || '-');
            $('#detail_guide_client').text([r.data.cliente, r.data.cliente_documento].filter(Boolean).join(' - '));
            $('#detail_guide_transport_mode').text(r.data.modo_transporte || '-');
            $('#detail_guide_origin').text(r.data.partida || '-');
            $('#detail_guide_destination').text(r.data.llegada || '-');
            $('#detail_guide_main_plate').text(r.data.placa_vehiculo || '-');
            $('#detail_guide_secondary_plate').text(r.data.placa_secundaria || '-');
            $('#detail_guide_driver').text(r.data.conductor_nombre || '-');
            $('#detail_guide_carrier').text(r.data.transportista_nombre || '-');

            let rows = '';
            (r.data.items || []).forEach(item => {
                rows += `<tr><td>${item.descripcion}</td><td>${item.codigo || '-'}</td><td>${item.unidad || '-'}</td><td>${item.cantidad}</td></tr>`;
            });
            $('#detail_guide_items').html(rows || '<tr><td colspan="4" class="text-center text-muted">Sin items</td></tr>');
            $('#modalDetailShipmentGuide').modal('show');
        }, 'json').fail(function (xhr) {
            toast_msg(xhr.responseJSON?.msg || 'No se pudo cargar el detalle.', xhr.responseJSON?.type || 'warning');
        });
    });

    function printGuideDocument(url, baseAsset, id) {
        $.ajax({
            url: url,
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", id: id },
            beforeSend: function () { block_content('#layout-content'); },
            success: function (r) {
                close_block('#layout-content');
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                const pdf = `${baseAsset}/${r.pdf}`;
                if (/Mobi|Android/i.test(navigator.userAgent)) {
                    const link = document.createElement('a');
                    link.href = pdf;
                    link.download = r.pdf;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    return;
                }

                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = pdf;
                document.body.appendChild(iframe);
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            },
            error: function (xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo generar el documento.', xhr.responseJSON?.type || 'warning');
            },
            dataType: 'json'
        });
    }

    $('body').on('click', '.btn-ticket-guide', function () {
        printGuideDocument("{{ route('admin.print_shipment_guide_ticket') }}", "{{ asset('files/shipment_guides/ticket') }}", $(this).data('id'));
    });

    $('body').on('click', '.btn-a4-guide', function () {
        printGuideDocument("{{ route('admin.print_shipment_guide_a4') }}", "{{ asset('files/shipment_guides/a4') }}", $(this).data('id'));
    });
</script>
