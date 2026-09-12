<script>
    let currentArchingData = null;

    $(function () {
        @if (session('message'))
            toast_msg(@json(session('message')), @json(session('message_type', 'warning')));
        @endif
    });

    function formatMoney(value) {
        const amount = Number(value || 0);
        return `{{ $signo ?? 'S/' }} ${amount.toFixed(2)}`;
    }

    function printArchingTicket(id) {
        $.ajax({
            url: "{{ route('admin.print_summary') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            beforeSend: function () {
                block_content('#layout-content');
            },
            success: function (r) {
                close_block('#layout-content');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                const pdf = `{{ asset('files/arching-cashes/ticket') }}/${r.pdf}`;

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
            error: function () {
                close_block('#layout-content');
                toast_msg('No se pudo generar el ticket del arqueo.', 'error');
            },
            dataType: 'json'
        });
    }

    function renderPaymentSummary(rows) {
        const body = $('#arching_payments_body');
        body.empty();

        if (!rows || !rows.length) {
            body.html('<tr><td colspan="2" class="text-center text-muted py-4">Sin movimientos registrados.</td></tr>');
            return;
        }

        rows.forEach((row) => {
            body.append(`
                <tr>
                    <td class="ps-3">${row.label}</td>
                    <td class="text-end pe-3 fw-semibold">{{ $signo ?? 'S/' }} ${Number(row.total).toFixed(2)}</td>
                </tr>
            `);
        });
    }

    function fillArchingSummaryModal(response) {
        currentArchingData = response;

        $('#detail_arching_cash_id').val(response.archingCash.id);
        $('#detail_cash_name').text(response.archingCash.cash || '-');
        $('#detail_cash_warehouse').text(response.archingCash.warehouse || '-');
        $('#detail_cash_user').text(response.archingCash.responsable || '-');
        $('#detail_cash_status').text(response.archingCash.estado === 1 ? 'Abierta' : 'Cerrada');

        // Resumen Financiero
        $('#detail_opening_amount').text(formatMoney(response.summary.opening_amount));
        $('#detail_sales_count').text(response.summary.sales_count || 0);
        $('#detail_sales_total').text(formatMoney(response.summary.sales_total));
        $('#detail_cash_total').text(formatMoney(response.summary.cash_total));
        $('#detail_digital_total').text(formatMoney(response.summary.digital_total));
        $('#detail_expected_cash').text(formatMoney(response.summary.expected_cash));
        $('#detail_gross_total').text(formatMoney(response.summary.gross_total));
        $('#detail_annulled_count').text(response.summary.annulled_count || 0);
        $('#detail_annulled_total').text(formatMoney(response.summary.annulled_total));

        // Cuadre de Bidones / Envases (4 buckets)
        $('#detail_jugs_intact').text(response.summary.jugs_intact || 0);
        $('#detail_jugs_damaged').text(response.summary.jugs_damaged || 0);
        $('#detail_jugs_loaned').text(response.summary.jugs_loaned || 0);
        $('#detail_jugs_sold').text(response.summary.jugs_sold || 0);

        if (Number(response.summary.damage_cost || 0) > 0) {
            $('#detail_damage_cost').text(formatMoney(response.summary.damage_cost));
            $('#detail_damage_cost_row').removeClass('d-none');
        } else {
            $('#detail_damage_cost_row').addClass('d-none');
        }

        // Operaciones y Entregas
        $('#detail_total_orders').text(response.summary.total_orders || 0);
        $('#detail_total_deliveries').text(response.summary.total_deliveries || 0);
        $('#detail_deliveries_collected').text(formatMoney(response.summary.deliveries_collected));

        // Boton de Cierre dentro del modal de detalle
        if (response.archingCash.can_close) {
            $('#btn-detail-close-cash').removeClass('d-none').data('id', response.archingCash.id);
        } else {
            $('#btn-detail-close-cash').addClass('d-none');
        }

        renderPaymentSummary(response.summary.payment_summary || []);
        load_arching_movements_datatable(response.archingCash.id);
        $('#modalDetailArchingCash').modal('show');
    }

    function fetchArchingSummary(id, callback) {
        $.ajax({
            url: "{{ route('admin.get_detail_cash') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            beforeSend: function () {
                block_content('#layout-content');
            },
            success: function (r) {
                close_block('#layout-content');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                currentArchingData = r;
                if (typeof callback === 'function') {
                    callback(r);
                } else {
                    fillArchingSummaryModal(r);
                }
            },
            error: function () {
                close_block('#layout-content');
                toast_msg('No se pudo cargar el resumen de caja.', 'error');
            },
            dataType: 'json'
        });
    }

    function openCloseCashModal(id) {
        const setupModal = function(data) {
            const expectedCash = parseFloat(data.summary.expected_cash || data.summary.expected_final || 0);
            $('#close_cash_id').val(data.archingCash.id);
            $('#close_cash_name').text(data.archingCash.cash || '-');
            $('#close_cash_user').text(data.archingCash.responsable || '-');
            $('#close_cash_opening').text(formatMoney(data.summary.opening_amount));
            $('#close_cash_expected').text(formatMoney(expectedCash));

            $('#close_cash_counted')
                .val('')
                .data('expected', expectedCash)
                .removeClass('is-invalid');

            $('#close_cash_diff_alert').addClass('d-none');

            $('#modalDetailArchingCash').modal('hide');
            $('#modalCloseArchingCash').modal('show');
            setTimeout(() => $('#close_cash_counted').focus(), 400);
        };

        if (currentArchingData && currentArchingData.archingCash && (Number(currentArchingData.archingCash.id) === Number(id))) {
            setupModal(currentArchingData);
        } else {
            fetchArchingSummary(id, setupModal);
        }
    }

    // Calculo de diferencia en vivo al ingresar el dinero fisico
    $('#close_cash_counted').on('input', function () {
        const countedVal = $(this).val().trim();
        const alertBox = $('#close_cash_diff_alert');
        const diffText = $('#close_cash_diff_text');

        if (countedVal === '' || isNaN(countedVal)) {
            alertBox.addClass('d-none');
            return;
        }

        const counted = parseFloat(countedVal) || 0;
        const expected = parseFloat($(this).data('expected')) || 0;
        const diff = counted - expected;
        const diffAbs = Math.abs(diff).toFixed(2);

        alertBox.removeClass('d-none alert-secondary alert-success alert-danger alert-info');

        if (Math.abs(diff) < 0.01) {
            alertBox.addClass('alert-success');
            diffText.html('<i class="ri-checkbox-circle-line me-1"></i>¡Caja exactamente cuadrada! Diferencia: S/ 0.00');
        } else if (diff < 0) {
            alertBox.addClass('alert-danger');
            diffText.html(`<i class="ri-error-warning-line me-1"></i>Faltante en gaveta: S/ ${diffAbs}`);
        } else {
            alertBox.addClass('alert-info');
            diffText.html(`<i class="ri-information-line me-1"></i>Sobrante en gaveta: S/ ${diffAbs}`);
        }
    });

    // Confirmacion y envio de cierre de caja
    $('body').on('click', '.btn-confirm-close', function (event) {
        event.preventDefault();
        const button = $(this);
        const cashId = $('#close_cash_id').val();
        const countedVal = $('#close_cash_counted').val().trim();

        if (countedVal === '' || isNaN(countedVal) || Number(countedVal) < 0) {
            $('#close_cash_counted').addClass('is-invalid').focus();
            toast_msg('Debe ingresar el dinero físico real contado en caja.', 'warning');
            return;
        }

        $('#close_cash_counted').removeClass('is-invalid');

        $.ajax({
            url: "{{ route('admin.close_cash') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: cashId,
                monto_real: countedVal
            },
            beforeSend: function () {
                button.prop('disabled', true);
                button.find('.text-close-btn').addClass('d-none');
                button.find('.text-closing-btn').removeClass('d-none');
            },
            success: function (r) {
                button.prop('disabled', false);
                button.find('.text-close-btn').removeClass('d-none');
                button.find('.text-closing-btn').addClass('d-none');

                toast_msg(r.msg, r.type);

                if (r.status) {
                    $('#modalCloseArchingCash').modal('hide');
                    setTimeout(() => window.location.reload(), 450);
                }
            },
            error: function (xhr) {
                button.prop('disabled', false);
                button.find('.text-close-btn').removeClass('d-none');
                button.find('.text-closing-btn').addClass('d-none');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo cerrar la caja.', 'error');
            },
            dataType: 'json'
        });
    });

    // Eventos de apertura de modal de cierre
    $('body').on('click', '.btn-prompt-close, .btn-close-arching', function (event) {
        event.preventDefault();
        const id = $(this).data('id');
        openCloseCashModal(id);
    });

    $('body').on('click', '#btn-detail-close-cash', function (event) {
        event.preventDefault();
        const id = $(this).data('id') || $('#detail_arching_cash_id').val();
        openCloseCashModal(id);
    });

    // Aperturar caja
    $('body').on('click', '.btn-create', function (event) {
        event.preventDefault();
        $('#form_save').trigger('reset');
        $('#form_save input[name="monto_inicial"]').val('0.00').removeClass('is-invalid');
        $('#modalArchingCash').modal('show');
    });

    $('body').on('click', '#modalArchingCash .btn-save', function (event) {
        event.preventDefault();

        const button = $(this);
        const form = $('#form_save').serialize();
        const amountInput = $('#form_save input[name="monto_inicial"]');
        const amount = amountInput.val().trim();

        if (amount === '' || isNaN(amount) || Number(amount) < 0) {
            amountInput.addClass('is-invalid').focus();
            toast_msg('Ingresa un monto inicial valido.', 'warning');
            return;
        }

        amountInput.removeClass('is-invalid');

        $.ajax({
            url: "{{ route('arching_cash.save') }}",
            method: 'POST',
            data: form,
            beforeSend: function () {
                button.prop('disabled', true);
                button.find('.text-save').addClass('d-none');
                button.find('.text-saving').removeClass('d-none');
            },
            success: function (r) {
                button.prop('disabled', false);
                button.find('.text-save').removeClass('d-none');
                button.find('.text-saving').addClass('d-none');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#modalArchingCash').modal('hide');
                $('#form_save').trigger('reset');
                toast_msg(r.msg, r.type);
                setTimeout(() => window.location.reload(), 450);
            },
            error: function (xhr) {
                button.prop('disabled', false);
                button.find('.text-save').removeClass('d-none');
                button.find('.text-saving').addClass('d-none');

                const message = xhr.responseJSON?.msg || 'No se pudo aperturar la caja.';
                toast_msg(message, 'error');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-view-summary, .btn-view-movements', function (event) {
        event.preventDefault();
        fetchArchingSummary($(this).data('id'));
    });

    $('body').on('click', '.btn-print-summary', function (event) {
        event.preventDefault();
        printArchingTicket($(this).data('id'));
    });

    $('body').on('click', '#btn-print-summary', function (event) {
        event.preventDefault();
        const id = $('#detail_arching_cash_id').val();

        if (!id) {
            toast_msg('No hay un arqueo seleccionado para imprimir.', 'warning');
            return;
        }

        printArchingTicket(id);
    });
</script>
