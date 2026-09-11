<script>
    $('body').on('click', '.btn-ticket', function()
    {
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.print_sale_note') }}",
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
                let pdf                 =   `{{ asset('files/sale-notes/ticket/${r.pdf}') }}`;
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

    $('body').on('click', '.btn-pdf', function() {
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.print_sale_note_a4') }}",
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
                let pdf                 =   `{{ asset('files/sale-notes/a4/${r.pdf}') }}`;
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
        title: "¿Desea anular la venta?",
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
                url: "{{ route('admin.anulled_sale_note') }}", // Mantiene la ruta original
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
                    if (r.status) {
                        reload_table();
                    }
                },
                error: function() {
                    Swal.fire("Error", "Hubo un problema en la solicitud", "error");
                }
            });
        }
    });
});

</script>