<script>
    let tableLoyalty = null;

    function initLoyaltyTable() {
        tableLoyalty = $('#table-loyalty-clients').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('loyalty.get_clients') }}",
            columns: [
                { data: 'nro_documento', name: 'clients.nro_documento' },
                { data: 'nombres', name: 'clients.nombres', className: 'fw-semibold text-dark' },
                { data: 'telefono', name: 'clients.telefono' },
                { data: 'progreso', name: 'progreso', orderable: false, searchable: false },
                { data: 'estado_premio', name: 'estado_premio', orderable: false, searchable: false, className: 'text-center' },
                { data: 'premios_reclamados', name: 'client_loyalty.premios_reclamados', className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[3, 'desc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    }

    $(document).ready(function() {
        initLoyaltyTable();

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Guardar configuración de la promoción
        $('#form-loyalty-settings').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('loyalty.save_settings') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(r) {
                    toast_msg(r.msg, 'success');
                    tableLoyalty.ajax.reload();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'Error al guardar configuración', 'error');
                }
            });
        });

        // Canjear premio manualmente
        $(document).on('click', '.btn-redeem-reward', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            Swal.fire({
                title: '¿Canjear bidón gratis?',
                html: `Se descontará la meta de compras acumuladas de <strong>${name}</strong> y se registrará el canje del bidón gratuito.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0061f2',
                cancelButtonColor: '#6e7881',
                confirmButtonText: 'Sí, canjear premio',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('loyalty.redeem_reward') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", idcliente: id },
                        success: function(r) {
                            if (r.status) {
                                toast_msg(r.msg, 'success');
                                tableLoyalty.ajax.reload();
                            } else {
                                toast_msg(r.msg, 'warning');
                            }
                        },
                        error: function(xhr) {
                            toast_msg('Error al procesar canje', 'error');
                        }
                    });
                }
            });
        });

        // Sumar compra/punto manual
        $(document).on('click', '.btn-add-loyalty-point', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Sumar compras acumuladas',
                html: `Sumar bidones acumulados para <strong>${name}</strong>:`,
                input: 'number',
                inputValue: 1,
                inputAttributes: { min: 1, max: 20, step: 1 },
                showCancelButton: true,
                confirmButtonText: 'Sumar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: "{{ route('loyalty.add_point') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", idcliente: id, cantidad: result.value },
                        success: function(r) {
                            toast_msg(r.msg, 'success');
                            tableLoyalty.ajax.reload();
                        },
                        error: function(xhr) {
                            toast_msg('Error al sumar compras', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
