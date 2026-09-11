<script>
     $('body').on('click', '.btn-pdf', function() {
        event.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.print_buy') }}",
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
                let pdf = `{{ asset('files/buys/${r.pdf}') }}`;
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
        title: "¿Está seguro?",
        text: "¿Desea eliminar este registro?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33", // Rojo para eliminar
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.delete_buy') }}",
                method: 'POST',
                data: {
                    '_token': "{{ csrf_token() }}",
                    id: id
                },
                success: function(r) {
                    if (!r.status) {
                        toastr.error(r.msg, "Error");
                        return;
                    }

                    toastr.success(r.msg, "Éxito");
                    reload_table();
                },
                error: function() {
                    toastr.error("Hubo un problema al intentar eliminar el registro.", "Error");
                },
                dataType: 'json'
            });
        }
    });
});
</script>