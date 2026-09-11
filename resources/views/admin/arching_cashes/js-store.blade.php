<script>
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
                    <td>${row.label}</td>
                    <td class="text-end fw-semibold">{{ $signo ?? 'S/' }} ${row.total}</td>
                </tr>
            `);
        });
    }

    function fillArchingSummaryModal(response) {
        $('#detail_arching_cash_id').val(response.archingCash.id);
        $('#detail_cash_name').text(response.archingCash.cash || '-');
        $('#detail_cash_warehouse').text(response.archingCash.warehouse || '-');
        $('#detail_cash_user').text(response.archingCash.responsable || '-');
        $('#detail_cash_status').text(response.archingCash.estado === 1 ? 'Abierta' : 'Cerrada');

        $('#detail_opening_amount').text(formatMoney(response.summary.opening_amount));
        $('#detail_sales_count').text(response.summary.sales_count || 0);
        $('#detail_sales_total').text(formatMoney(response.summary.sales_total));
        $('#detail_gross_total').text(formatMoney(response.summary.gross_total));
        $('#detail_annulled_total').text(formatMoney(response.summary.annulled_total));
        $('#detail_annulled_count').text(response.summary.annulled_count || 0);
        $('#detail_expenses_total').text(formatMoney(response.summary.expenses_total));
        $('#detail_expenses_count').text(response.summary.expenses_count || 0);
        $('#detail_final_amount').text(formatMoney(response.summary.display_final));

        renderPaymentSummary(response.summary.payment_summary || []);

        load_arching_movements_datatable(response.archingCash.id);
        $('#modalDetailArchingCash').modal('show');
    }

    function fetchArchingSummary(id) {
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

                fillArchingSummaryModal(r);
            },
            error: function () {
                close_block('#layout-content');
                toast_msg('No se pudo cargar el resumen de caja.', 'error');
            },
            dataType: 'json'
        });
    }

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

    $('body').on('click', '.btn-close-arching', function (event) {
        event.preventDefault();
        const id = $(this).data('id');

        Swal.fire({
            title: 'Confirmar cierre de caja',
            text: 'Se cerrara la caja usando el total acumulado del arqueo actual.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Si, cerrar caja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: "{{ route('admin.close_cash') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Cerrando caja...',
                        text: 'Por favor, espera un momento.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },
                success: function (r) {
                    Swal.close();
                    toast_msg(r.msg, r.type);

                    if (r.status) {
                        $('#modalDetailArchingCash').modal('hide');
                        setTimeout(() => window.location.reload(), 450);
                    }
                },
                error: function (xhr) {
                    Swal.close();
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo cerrar la caja.', 'error');
                },
                dataType: 'json'
            });
        });
    });
</script>
