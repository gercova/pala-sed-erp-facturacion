<script>
    let tableJugBalances = null;
    let tableJugMovements = null;

    function initJugTables() {
        tableJugBalances = $('#table-jug-balances').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('jug_movements.get') }}",
            columns: [
                { data: 'nro_documento', name: 'nro_documento' },
                { data: 'nombres', name: 'nombres', className: 'fw-semibold text-dark' },
                { data: 'telefono', name: 'telefono' },
                { data: 'direccion', name: 'direccion' },
                { data: 'status_badge', name: 'status_badge', className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[4, 'desc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

        tableJugMovements = $('#table-jug-movements').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('jug_movements.get_movements') }}",
            columns: [
                { data: 'fecha', name: 'fecha' },
                { data: 'cliente', name: 'cliente' },
                { data: 'tipo_movimiento', name: 'tipo_movimiento', className: 'text-center' },
                { data: 'detalle_cantidades', name: 'detalle_cantidades', orderable: false },
                { data: 'saldo_anterior', name: 'saldo_anterior', className: 'text-center' },
                { data: 'saldo_nuevo', name: 'saldo_nuevo', className: 'text-center fw-bold' },
                { data: 'observaciones', name: 'observaciones' }
            ],
            order: [[0, 'desc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    }

    $(document).ready(function() {
        initJugTables();

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Abrir modal de devolución directa
        $('.btn-direct-return').on('click', function() {
            $('#form-direct-return')[0].reset();
            $('#modal-direct-return').modal('show');
        });

        // Abrir devolución directa desde la fila de un cliente
        $(document).on('click', '.btn-record-return', function() {
            let id = $(this).data('id');
            let balance = $(this).data('balance');

            $('#form-direct-return')[0].reset();
            $('#return_idcliente').val(id);
            $('#return_intact').val(balance > 0 ? balance : 1);
            $('#modal-direct-return').modal('show');
        });

        // Enviar devolución directa
        $('#form-direct-return').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('jug_movements.store_return') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(r) {
                    $('#modal-direct-return').modal('hide');
                    toast_msg(r.msg, 'success');
                    tableJugBalances.ajax.reload();
                    tableJugMovements.ajax.reload();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'Error al registrar devolución', 'error');
                }
            });
        });

        // Abrir modal de ajuste manual
        $(document).on('click', '.btn-adjust-balance', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let balance = $(this).data('balance');

            $('#adjust_idcliente').val(id);
            $('#adjust_client_name').text(name);
            $('#adjust_current_balance').text(balance);
            $('#adjust_new_balance').val(balance);
            $('#modal-adjust-balance').modal('show');
        });

        // Enviar ajuste manual
        $('#form-adjust-balance').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('jug_movements.adjust_balance') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(r) {
                    $('#modal-adjust-balance').modal('hide');
                    toast_msg(r.msg, 'success');
                    tableJugBalances.ajax.reload();
                    tableJugMovements.ajax.reload();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'Error al guardar ajuste', 'error');
                }
            });
        });

        // Ver historial de envases del cliente
        $(document).on('click', '.btn-client-history', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            $('#history_client_name').text(name);
            $('#history_movements_body').html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm"></div></td></tr>');
            $('#modal-client-history').modal('show');

            $.ajax({
                url: "{{ url('jug-movements/client-history') }}/" + id,
                method: "GET",
                success: function(r) {
                    let rows = '';
                    if (r.movements.length === 0) {
                        rows = '<tr><td colspan="7" class="text-center py-3 text-muted">Sin movimientos registrados.</td></tr>';
                    } else {
                        r.movements.forEach(m => {
                            let dateFormatted = new Date(m.fecha).toLocaleString();
                            rows += `
                                <tr>
                                    <td class="small">${dateFormatted}</td>
                                    <td><span class="badge bg-light text-dark">${m.tipo_movimiento}</span></td>
                                    <td class="text-center text-primary font-monospace">+${m.entregados_llenos}</td>
                                    <td class="text-center text-success font-monospace">-${m.devueltos_intactos}</td>
                                    <td class="text-center text-danger font-monospace">-${m.devueltos_danados}</td>
                                    <td class="text-center fw-bold">${m.saldo_nuevo}</td>
                                    <td class="small text-muted">${m.observaciones || '-'}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#history_movements_body').html(rows);
                }
            });
        });
    });
</script>
