<script>
    $('body').on('click', '.btn-confirm-transfer', function() {
        event.preventDefault();
        let id  = $(this).data('id');
        $.ajax({
            url         : "{{ route('admin.detail_transfer') }}",
            method      : "POST",
            data        : {
                '_token': "{{ csrf_token() }}",
                id      : id
            },
            beforeSend  : function(){
                block_content('#layout-content');
            },
            success     : function(r){
                if(!r.status)
                {
                    close_block('#layout-content');
                    toast_msg(r.msg, r.type);
                    return;
                }
                close_block('#layout-content');
                let detail__transfer = '';
                $('input[name="idtransfer"]').val(r.transfer.id);
                $('.nro__orden').text(r.transfer.serie + '-' + r.transfer.correlativo);
                $('.td__emision').text(r.fecha_emision);
                $('.td__expiration').text(r.fecha_vencimiento);
                $('.td__dispatch').text(r.transfer.almacen_despacho);
                $('.td__receiver').text(r.transfer.almacen_receptor);
                $('.td__optional').text((r.transfer.observaciones || '').trim() !== '' ? r.transfer.observaciones : 'Sin observaciones registradas');
              
                $.each(r.detail_transfer, function(index, transfer){
                    detail__transfer += `<tr>
                                            <td class="text-center">${index + 1}</td>
                                            <td class="text-start">${(transfer.codigo_interno == null || transfer.codigo_interno == "-") ? "-" : transfer.codigo_interno}</td>
                                            <td class="text-start fw-semibold">${transfer.producto}</td>
                                            <td class="text-center">${transfer.cantidad}</td>
                                        </tr>`;
                });

                $('#tbody_detail_transfer').html(detail__transfer);
                $('#modalConfirmTransfer').modal('show');
            },
            dataType    : "json"
        });
    });

    $('body').on('click', '.btn-save', function() {
        event.preventDefault();
        let form    = $('#form_save').serialize();
        $.ajax({
            url         : "{{ route('admin.move_transfer') }}",
            method      : "POST",
            data        : form,
            beforeSend  : function(){
                $('.btn-save').prop('disabled', true);
                $('.text-save').addClass('d-none');
                $('.text-saving').removeClass('d-none');
            },
            success     : function(r)
            {
                $('.btn-save').prop('disabled', false);
                $('.text-save').removeClass('d-none');
                $('.text-saving').addClass('d-none');
                if(!r.status)
                {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#modalConfirmTransfer').modal('hide');
                reload_table();

                let pdf                 =   `{{ asset('files/transfer-orders/${r.pdf}') }}`;
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
            dataType    : "json"
        });
    });

    $('body').on('click', '.btn-print', function()
    {
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.print_transfer') }}",
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
                    toast_msg(r.msg, r.type);
                    return;
                }
         
                let pdf                 =   `{{ asset('files/transfer-orders/${r.pdf}') }}`;
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

    $('body').on('click', '.btn-confirm', function(event) {
    event.preventDefault();
    let id = $(this).data('id');

    Swal.fire({
        title: "¿Desea anular la orden?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, anular",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.anulled_tansfer_order') }}",
                method: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    id: id
                },
                beforeSend: function() {
                    Swal.fire({
                        title: "Anulando...",
                        text: "Por favor, espere",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(r) {
                        Swal.close();
                        toast_msg(r.msg, r.type);
                        if (!r.status) {
                            return;
                        }
                        reload_table();
                    },
                    error: function() {
                        Swal.fire("Error", "Hubo un problema en la solicitud", "error");
                    }
                });
            }
        });
    });

</script>
