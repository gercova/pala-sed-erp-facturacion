<script>
    $('body').on('click', '.btn-detail-quote', function(event) {
        event.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: "{{ route('quotes.detail') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: id
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');

                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                $('#quote-detail-document').text(`${r.quote.serie}-${r.quote.correlativo}`);
                $('#quote-detail-date').text(r.fecha_emision || '-');
                $('#quote-detail-customer').text(r.quote.cliente || '-');
                $('#quote-detail-total').text(`{{ $signo ?? 'S/' }} ${parseFloat(r.quote.total || 0).toFixed(2)}`);

                let detailHtml = '';
                $.each(r.detail, function(index, item) {
                    detailHtml += `
                        <tr>
                            <td>${item.producto}</td>
                            <td class="text-center">${item.unidad || '-'}</td>
                            <td class="text-center">${parseFloat(item.cantidad || 0).toFixed(2)}</td>
                            <td class="text-center">${parseFloat(item.precio_unitario || 0).toFixed(2)}</td>
                            <td class="text-center">${parseFloat(item.precio_total || 0).toFixed(2)}</td>
                        </tr>
                    `;
                });

                $('#quote-detail-body').html(detailHtml || '<tr><td colspan="5" class="text-center text-muted">Sin detalle</td></tr>');
                $('#modalQuoteDetail').modal('show');
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo cargar el detalle de la cotizacion.', xhr.responseJSON?.type || 'error');
            },
            dataType: "json"
        });
    });

     $('body').on('click', '.btn-pdf', function(event) {
        event.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.print_quote_a4') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: id
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                if (!r.status) {
                    close_block('#layout-content');
                    toast_msg(r.msg, r.type);
                    return;
                }
                close_block('#layout-content');
                let pdf = `{{ asset('files/quotes/${r.pdf}') }}`;
                if (/Mobi|Android/i.test(navigator.userAgent)) {
                        // Dispositivo móvil: descarga el PDF
                        let link = document.createElement('a');
                        link.href = pdf;
                        link.download = r.pdf; // Opcional: nombre del archivo
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        // Dispositivo de escritorio: muestra e imprime el PDF
                        var iframe = document.createElement('iframe');
                        iframe.style.display = "none";
                        iframe.src = pdf;
                        document.body.appendChild(iframe);
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }
            },
            dataType: "json"
        });
    });
</script>
