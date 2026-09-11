<script>
    function buildSunatMessage(response, fallbackMessage) {
        const message = response?.msg || fallbackMessage;
        const detail = (response?.detail || '').trim();

        return detail ? `${message}\n${detail}` : message;
    }

    $('body').on('click', '.btn-a4', function() {
        let id = $(this).data('id');

        $.ajax({
            url: "{{ route('admin.print_billing_a4') }}",
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
                let pdf = `{{ asset('files/billings/a4/${r.pdf}') }}`;

                if (/Mobi|Android/i.test(navigator.userAgent)) {
                    let link = document.createElement('a');
                    link.href = pdf;
                    link.download = r.pdf;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    let iframe = document.createElement('iframe');
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

    $('body').on('click', '.btn-ticket', function() {
        let id = $(this).data('id');

        $.ajax({
            url: "{{ route('admin.print_billing_ticket') }}",
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
                let pdf = `{{ asset('files/billings/ticket/${r.pdf}') }}`;

                if (/Mobi|Android/i.test(navigator.userAgent)) {
                    let link = document.createElement('a');
                    link.href = pdf;
                    link.download = r.pdf;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    let iframe = document.createElement('iframe');
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

    $('body').on('click', '.btn-dispatch', function() {
        let id = $(this).data('id');

        $.ajax({
            url: `{{ url('billings') }}/${id}/dispatch`,
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}"
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');
                toast_msg(buildSunatMessage(r, 'Se proceso el envio a SUNAT.'), r.type || 'success');
                $('#table').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(
                    buildSunatMessage(xhr.responseJSON, 'No se pudo procesar el envio a SUNAT.'),
                    xhr.responseJSON?.type || 'warning'
                );
                $('#table').DataTable().ajax.reload(null, false);
            },
            dataType: "json"
        });
    });

    $('body').on('click', '.btn-credit-note-billing', function() {
        const id = $(this).data('id');
        const documentLabel = $(this).data('document');

        $('#credit_note_billing_id').val(id);
        $('#credit_note_type_id').val('');
        $('#credit_note_reason').val('');
        $('#creditNoteBillingLabel').text(documentLabel
            ? `Vas a anular el comprobante ${documentLabel} con una nota de credito.`
            : 'Selecciona el motivo de anulacion.');

        $('#modalCreditNoteBilling').modal('show');
    });

    $('body').on('click', '.btn-debit-note-billing', function() {
        const id = $(this).data('id');
        const documentLabel = $(this).data('document');

        $('#debit_note_billing_id').val(id);
        $('#debit_note_type_id').val('');
        $('#debit_note_reason').val('');
        $('#debitNoteBillingLabel').text(documentLabel
            ? `Vas a emitir una nota de debito sobre el comprobante ${documentLabel}.`
            : 'Selecciona el motivo del ajuste.');

        $('#modalDebitNoteBilling').modal('show');
    });

    $('body').on('click', '#btnSaveCreditNoteBilling', function() {
        const id = $('#credit_note_billing_id').val();
        const typeId = $('#credit_note_type_id').val();
        const reason = $('#credit_note_reason').val().trim();

        if (!id) {
            toast_msg('No se encontro el comprobante a anular.', 'warning');
            return;
        }

        if (!typeId) {
            toast_msg('Selecciona el motivo de la nota de credito.', 'warning');
            return;
        }

        if (reason === '') {
            toast_msg('Debes indicar el motivo de la anulacion.', 'warning');
            return;
        }

        $.ajax({
            url: `{{ url('billings') }}/${id}/credit-note`,
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                credit_note_type_id: typeId,
                reason: reason
            },
            beforeSend: function() {
                $('#btnSaveCreditNoteBilling').prop('disabled', true);
                $('.credit-note-save-text').addClass('d-none');
                $('.credit-note-save-loading').removeClass('d-none');
                $('.credit-note-save-spinner').removeClass('d-none');
            },
            success: function(r) {
                $('#btnSaveCreditNoteBilling').prop('disabled', false);
                $('.credit-note-save-text').removeClass('d-none');
                $('.credit-note-save-loading').addClass('d-none');
                $('.credit-note-save-spinner').addClass('d-none');

                toast_msg(
                    buildSunatMessage(r, 'Se proceso la nota de credito.'),
                    r.type || (r.status ? 'success' : 'warning')
                );
                $('#modalCreditNoteBilling').modal('hide');
                $('#table').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                $('#btnSaveCreditNoteBilling').prop('disabled', false);
                $('.credit-note-save-text').removeClass('d-none');
                $('.credit-note-save-loading').addClass('d-none');
                $('.credit-note-save-spinner').addClass('d-none');

                toast_msg(
                    buildSunatMessage(xhr.responseJSON, 'No se pudo emitir la nota de credito.'),
                    xhr.responseJSON?.type || 'warning'
                );
                $('#table').DataTable().ajax.reload(null, false);
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '#btnSaveDebitNoteBilling', function() {
        const id = $('#debit_note_billing_id').val();
        const typeId = $('#debit_note_type_id').val();
        const reason = $('#debit_note_reason').val().trim();

        if (!id) {
            toast_msg('No se encontro el comprobante base.', 'warning');
            return;
        }

        if (!typeId) {
            toast_msg('Selecciona el motivo de la nota de debito.', 'warning');
            return;
        }

        if (reason === '') {
            toast_msg('Debes indicar el motivo del ajuste.', 'warning');
            return;
        }

        $.ajax({
            url: `{{ url('billings') }}/${id}/debit-note`,
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                debit_note_type_id: typeId,
                reason: reason
            },
            beforeSend: function() {
                $('#btnSaveDebitNoteBilling').prop('disabled', true);
                $('.debit-note-save-text').addClass('d-none');
                $('.debit-note-save-loading').removeClass('d-none');
                $('.debit-note-save-spinner').removeClass('d-none');
            },
            success: function(r) {
                $('#btnSaveDebitNoteBilling').prop('disabled', false);
                $('.debit-note-save-text').removeClass('d-none');
                $('.debit-note-save-loading').addClass('d-none');
                $('.debit-note-save-spinner').addClass('d-none');

                toast_msg(
                    buildSunatMessage(r, 'Se proceso la nota de debito.'),
                    r.type || (r.status ? 'success' : 'warning')
                );
                $('#modalDebitNoteBilling').modal('hide');
                $('#table').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                $('#btnSaveDebitNoteBilling').prop('disabled', false);
                $('.debit-note-save-text').removeClass('d-none');
                $('.debit-note-save-loading').addClass('d-none');
                $('.debit-note-save-spinner').addClass('d-none');

                toast_msg(
                    buildSunatMessage(xhr.responseJSON, 'No se pudo emitir la nota de debito.'),
                    xhr.responseJSON?.type || 'warning'
                );
                $('#table').DataTable().ajax.reload(null, false);
            },
            dataType: 'json'
        });
    });
</script>
