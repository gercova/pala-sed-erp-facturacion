<script>
    let setTimeOutBuscador = '';
    let payModes = [];
    let saleDocumentTypes = [];
    let allClients = [];
    let reopenConfirmAfterClientModal = false;
    let paymentCondition = 'contado';
    let posTotals = {
        signo: 'S/',
        subtotal: 0,
        igv: 0,
        total: 0
    };

    const clientDocMap = {
        '1': '1',
        '2': '6',
        '3': '4',
        '4': '0',
        '5': '7',
        '6': 'A'
    };

    function money(value) {
        return `${posTotals.signo} ${parseFloat(value || 0).toFixed(2)}`;
    }

    function toNumber(value) {
        const parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function getSelectedDocumentCode() {
        return String($('#select-document-type option:selected').data('code') || '').trim();
    }

    function getSelectedClientOption() {
        const clientId = $('#select-client').val();
        return clientId ? $('#select-client option[value="' + clientId + '"]') : null;
    }

    function getSelectedClientDocumentCode() {
        const option = getSelectedClientOption();
        if (!option || !option.length) {
            return '';
        }

        const clientDocId = String(option.data('doc-id') || '').trim();
        return clientDocMap[clientDocId] || '';
    }

    function getSelectedClientDocumentNumber() {
        const option = getSelectedClientOption();
        return option ? String(option.data('doc-number') || '').trim() : '';
    }

    function isValidClientForDocument() {
        const documentCode = getSelectedDocumentCode();
        const clientDocCode = getSelectedClientDocumentCode();
        const clientDocNumber = getSelectedClientDocumentNumber();

        if (!$('#select-client').val()) {
            return false;
        }

        if (documentCode === '02') {
            return true;
        }

        if (documentCode === '01') {
            return clientDocCode === '6' && /^\d{11}$/.test(clientDocNumber);
        }

        if (documentCode === '03') {
            return clientDocCode !== '' && clientDocNumber !== '';
        }

        return false;
    }

    function load_cart() {
        $.ajax({
            url: "{{ route('admin.load_cart_pos') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                $('#process-sale').prop('disabled', r.cart_products.products.length < 1);
                $('#tbody_pos').html(r.html_cart);
                $('#wrapper-totals').html(r.html_totales);
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo cargar el carrito.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    }

    function load_clients(selectedId = null) {
        $.ajax({
            url: "{{ route('admin.load_clients') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                allClients = r.clients || [];
                renderClients(selectedId);
                syncCheckoutUI();
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo cargar la lista de clientes.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    }

    function clientMatchesDocument(client, documentCode) {
        const docCode = clientDocMap[String(client.iddoc || '').trim()] || '';
        const docNumber = String(client.nro_documento || '').trim();

        if (documentCode === '01') {
            return docCode === '6' && /^\d{11}$/.test(docNumber);
        }

        if (documentCode === '03') {
            return docCode !== '' && docNumber !== '';
        }

        return true;
    }

    function renderClients(selectedId = null) {
        const documentCode = getSelectedDocumentCode();
        const visibleClients = allClients.filter((client) => clientMatchesDocument(client, documentCode));
        const $select = $('#select-client');
        let htmlClients = '<option value=""></option>';

        $.each(visibleClients, function(index, client) {
            htmlClients += `<option value="${client.id}" data-doc-id="${client.iddoc || ''}" data-doc-number="${client.nro_documento || ''}">${client.nro_documento + ' - ' + client.nombres}</option>`;
        });

        const currentValue = selectedId ? String(selectedId) : String($select.val() || '');
        $select.html(htmlClients);

        if (currentValue && $select.find(`option[value="${currentValue}"]`).length) {
            $select.val(currentValue);
        } else if ($select.find('option').length > 1) {
            $select.prop('selectedIndex', 1);
        } else {
            $select.val('');
        }

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.trigger('change.select2');
        } else {
            $select.select2({
                width: '100%',
                dropdownParent: $('#modalConfirmSale')
            });
        }
    }

    function fillDocumentTypeOptions(defaultId = null) {
        let html = '';
        saleDocumentTypes.forEach((documentType) => {
            html += `<option value="${documentType.id}" data-code="${documentType.codigo}">${documentType.descripcion}</option>`;
        });

        $('#select-document-type').html(html);

        const defaultDocument = defaultId
            ? String(defaultId)
            : String((saleDocumentTypes.find((item) => String(item.codigo) === '03') || saleDocumentTypes[0] || {}).id || '');

        $('#select-document-type').val(defaultDocument);
        syncDocumentChoiceButtons();
    }

    function paymentSelectOptionsHtml() {
        return `
            <option value="">- Seleccione -</option>
            ${payModes.map((mode) => `<option value="${mode.id}">${mode.descripcion}</option>`).join('')}
        `;
    }

    function createPaymentRow(amount = '', methodId = '') {
        return `
            <div class="payment-row-grid payment-row">
                <div>
                    <select class="form-select payment-method">
                        ${paymentSelectOptionsHtml()}
                    </select>
                </div>
                <div>
                    <input type="number" class="form-control payment-amount" placeholder="Monto" min="0" step="0.01" value="${amount}">
                </div>
                <div>
                    <button class="btn btn-outline-danger remove-payment" type="button">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
    }

    function createInstallmentRow(amount = '', dueDate = '') {
        return `
            <div class="installment-row">
                <div>
                    <input type="number" class="form-control installment-amount" placeholder="Monto" min="0" step="0.01" value="${amount}">
                </div>
                <div>
                    <input type="date" class="form-control installment-date" value="${dueDate}">
                </div>
                <div>
                    <button class="btn btn-outline-danger remove-installment" type="button">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
    }

    function getNextInstallmentDate() {
        const baseDate = new Date();
        baseDate.setDate(baseDate.getDate() + 1);

        const year = baseDate.getFullYear();
        const month = String(baseDate.getMonth() + 1).padStart(2, '0');
        const day = String(baseDate.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function resetPaymentRows() {
        $('#payment-methods').html(createPaymentRow(posTotals.total.toFixed(2), '1'));
        const $firstSelect = $('#payment-methods .payment-method').first();
        if ($firstSelect.find('option[value="1"]').length) {
            $firstSelect.val('1');
        }
        $('#payment-methods .remove-payment').first().addClass('d-none');
    }

    function resetInstallmentRows() {
        $('#installment-methods').html(createInstallmentRow(posTotals.total.toFixed(2), getNextInstallmentDate()));
    }

    function syncDocumentChoiceButtons() {
        const documentCode = getSelectedDocumentCode();
        $('.doc-choice').removeClass('is-active');
        $('.doc-choice[data-code="' + documentCode + '"]').addClass('is-active');
    }

    function syncPaymentConditionButtons() {
        $('.payment-condition-choice').removeClass('is-active');
        $('.payment-condition-choice[data-condition="' + paymentCondition + '"]').addClass('is-active');
        $('#cash-payment-box').toggleClass('is-active', paymentCondition === 'contado');
        $('#credit-payment-box').toggleClass('is-active', paymentCondition === 'credito');
    }

    function getSummaryState() {
        const baseTotal = toNumber(posTotals.total);
        const requestedDiscount = Math.max(0, toNumber($('#global-discount').val()));
        const discount = Math.min(requestedDiscount, baseTotal);
        const finalTotal = Math.max(baseTotal - discount, 0);
        const ratio = baseTotal > 0 ? (finalTotal / baseTotal) : 0;
        const netSubtotal = +(toNumber(posTotals.subtotal) * ratio).toFixed(2);
        const netIgv = +(toNumber(posTotals.igv) * ratio).toFixed(2);

        let coveredAmount = 0;
        if (paymentCondition === 'contado') {
            $('.payment-amount').each(function() {
                coveredAmount += toNumber($(this).val());
            });
        } else {
            $('.installment-amount').each(function() {
                coveredAmount += toNumber($(this).val());
            });
        }

        const balance = +(coveredAmount - finalTotal).toFixed(2);

        return {
            discount,
            finalTotal,
            netSubtotal,
            netIgv,
            coveredAmount,
            balance
        };
    }

    function syncDocumentTypeHelper() {
        const documentCode = getSelectedDocumentCode();
        let helper = 'El ticket se abrira automaticamente al confirmar la venta.';

        if (documentCode === '01') {
            helper = 'Factura requiere cliente con RUC valido.';
        } else if (documentCode === '03') {
            helper = 'Boleta lista para cliente documentado.';
        } else if (documentCode === '02') {
            helper = 'Nota de venta habilitada.';
        }

        $('#document-type-helper').text(helper);
    }

    function syncClientRuleBadge() {
        $('#client-rule-message').text('');
    }

    function syncSummary() {
        const state = getSummaryState();

        $('#summary-subtotal').text(money(posTotals.subtotal));
        $('#summary-discount').text(money(state.discount));
        $('#summary-net-subtotal').text(money(state.netSubtotal));
        $('#summary-igv').text(money(state.netIgv));
        $('#summary-total').text(money(state.finalTotal));
        $('#summary-paid').text(money(state.coveredAmount));
        $('#summary-balance')
            .text(`${state.balance >= 0 ? '' : '-'}${money(Math.abs(state.balance))}`)
            .toggleClass('text-success', state.balance >= 0)
            .toggleClass('text-danger', state.balance < 0);

        let statusText = 'Completa los datos del comprobante para continuar.';
        const clientValid = isValidClientForDocument();

        if (!$('#select-client').val()) {
            statusText = 'Selecciona un cliente.';
        } else if (!clientValid) {
            statusText = 'El cliente no cumple la regla del comprobante seleccionado.';
        } else if (paymentCondition === 'contado' && state.balance < 0) {
            statusText = 'El total pagado no cubre el total de la venta.';
        } else if (paymentCondition === 'credito' && Math.abs(state.balance) > 0.01) {
            statusText = 'La suma de cuotas debe coincidir con el total final.';
        } else {
            statusText = paymentCondition === 'contado'
                ? 'Venta lista para cobrarse al contado.'
                : 'Venta lista para registrarse al credito.';
        }

        $('#summary-status-text').text(statusText);
        $('#confirm-sale').prop('disabled', !canSubmitSale());
    }

    function syncCheckoutUI() {
        syncDocumentChoiceButtons();
        syncPaymentConditionButtons();
        syncDocumentTypeHelper();
        syncClientRuleBadge();
        syncSummary();
    }

    function canSubmitSale() {
        const state = getSummaryState();

        if (!$('#select-client').val() || !$('#select-document-type').val() || !isValidClientForDocument()) {
            return false;
        }

        if (paymentCondition === 'contado') {
            return state.balance >= 0 && buildPaymentsPayload().errorMessage === null;
        }

        return Math.abs(state.balance) <= 0.01 && buildInstallmentsPayload().errorMessage === null;
    }

    function buildPaymentsPayload() {
        const payments = [];
        let errorMessage = null;

        $('#payment-methods .payment-row').each(function() {
            const paymentMethod = $(this).find('.payment-method').val();
            const paymentAmount = toNumber($(this).find('.payment-amount').val());

            if (!paymentMethod && paymentAmount === 0) {
                return;
            }

            if (!paymentMethod || paymentAmount <= 0) {
                errorMessage = 'Completa correctamente todos los metodos de pago.';
                return false;
            }

            payments.push({
                method_id: paymentMethod,
                amount: paymentAmount.toFixed(2)
            });
        });

        if (!errorMessage && payments.length < 1) {
            errorMessage = 'Debe agregar al menos un metodo de pago valido.';
        }

        return {
            payments,
            errorMessage
        };
    }

    function buildInstallmentsPayload() {
        const installments = [];
        let errorMessage = null;

        $('#installment-methods .installment-row').each(function() {
            const amount = toNumber($(this).find('.installment-amount').val());
            const dueDate = String($(this).find('.installment-date').val() || '').trim();

            if (amount === 0 && dueDate === '') {
                return;
            }

            if (amount <= 0 || dueDate === '') {
                errorMessage = 'Completa correctamente todas las cuotas del credito.';
                return false;
            }

            installments.push({
                amount: amount.toFixed(2),
                due_date: dueDate
            });
        });

        if (!errorMessage && installments.length < 1) {
            errorMessage = 'Debe registrar al menos una cuota para la venta al credito.';
        }

        return {
            installments,
            errorMessage
        };
    }

    function open_sale_ticket(ticketUrl) {
        if (!ticketUrl) {
            toast_msg('No se encontro el ticket generado.', 'warning');
            return;
        }

        if (/Mobi|Android/i.test(navigator.userAgent)) {
            const link = document.createElement('a');
            link.href = ticketUrl;
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = ticketUrl;
            document.body.appendChild(iframe);
            iframe.onload = function() {
                iframe.contentWindow?.focus();
                iframe.contentWindow?.print();
            };
        }

        reset_pos_modal();
        load_cart();
    }

    function reset_pos_modal() {
        paymentCondition = 'contado';
        $('#modalConfirmSale').modal('hide');
        $('#global-discount').val('0.00');
        resetPaymentRows();
        resetInstallmentRows();

        const defaultDocument = saleDocumentTypes.find((item) => String(item.codigo) === '03') || saleDocumentTypes[0];
        if (defaultDocument) {
            $('#select-document-type').val(String(defaultDocument.id));
        }

        if ($('#select-client option').length > 1) {
            $('#select-client').prop('selectedIndex', 1).trigger('change');
        } else {
            syncCheckoutUI();
        }
    }

    $('.input-barcode').autocomplete({
        source: function(request, response) {
            const idalmacen = $('input[name="idalmacenuser"]').val();
            clearTimeout(setTimeOutBuscador);

            setTimeOutBuscador = setTimeout(() => {
                $.ajax({
                    url: "{{ route('admin.search_product_pos') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        search: request.term,
                        idalmacen: idalmacen
                    },
                    success: function(data) {
                        response(data.slice(0, 5));
                    },
                    dataType: 'json'
                });
            }, 200);
        },
        minLength: 1,
        open: function() {
            const $menu = $('.ui-autocomplete');
            const $input = $(this);
            $menu.css({
                width: $input.outerWidth() + 'px',
                maxWidth: $input.outerWidth() + 'px'
            });
        },
        focus: function(event, ui) {
            event.preventDefault();
            $('.input-barcode').val(ui.item.texto_limpio);
        },
        select: function(event, ui) {
            event.preventDefault();
            $('.input-barcode').val('');

            $.ajax({
                url: "{{ route('admin.add_product_search') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    idproducto: ui.item.idproducto,
                    idalmacen: ui.item.idalmacen
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

                    toast_msg(r.msg, r.type || 'success');
                    load_cart();
                },
                error: function(xhr) {
                    close_block('#layout-content');
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo agregar el producto.', xhr.responseJSON?.type || 'error');
                },
                dataType: 'json'
            });
        }
    }).autocomplete('instance')._renderItem = function(ul, item) {
        const codeHtml = item.codigo
            ? `<span class="badge bg-secondary px-2 py-1 rounded-pill me-1" style="font-size: 0.7rem;"><i class="fas fa-barcode"></i> ${item.codigo}</span>`
            : '';
        const marcaHtml = item.marca
            ? `<span class="text-muted ms-1" style="font-size: 0.8rem;"> &bull; ${item.marca}</span>`
            : '';

        let stockHtml = '';
        if (item.opcion == 1 && item.stock != null) {
            let stockColor = 'bg-success';
            if (item.stock <= 5) stockColor = 'bg-danger';
            else if (item.stock <= 10) stockColor = 'bg-warning text-dark';

            stockHtml = `<span class="badge ${stockColor} px-2 py-1 rounded-pill ms-1" style="font-size: 0.7rem;">Stock: ${item.stock}</span>`;
        }

        const content = `
            <div class="pos-autocomplete-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom" style="cursor: pointer; transition: all 0.2s;">
                <div class="d-flex flex-column text-truncate" style="width: 75%;">
                    <div class="fw-bold text-truncate" style="font-size: 0.95rem; color: #2c3e50;">
                        ${item.nombre} ${marcaHtml}
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        ${codeHtml}
                        ${stockHtml}
                    </div>
                </div>
                <div class="text-end" style="width: 25%;">
                    <span class="fw-bold" style="font-size: 1.1rem; color: #27ae60;">${item.precio}</span>
                </div>
            </div>
        `;

        const $li = $('<li>').append(content).appendTo(ul);
        $li.find('.pos-autocomplete-item').hover(
            function() { $(this).css('background-color', '#f1f5f9'); },
            function() { $(this).css('background-color', 'transparent'); }
        );

        return $li;
    };

    $('body').on('click', '.btn-delete-product', function(event) {
        event.preventDefault();
        const id = $(this).data('id');

        $.ajax({
            url: "{{ route('admin.delete_product_pos') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                load_cart();
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo eliminar el producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-down, .btn-up', function(event) {
        event.preventDefault();

        const id = $(this).data('id');
        const cantidad = parseInt($(this).data('cantidad'), 10);
        const cantidadEnviar = $(this).hasClass('btn-up') ? cantidad + 1 : cantidad - 1;
        const precio = parseFloat($(this).data('precio_venta'));

        if (cantidadEnviar <= 0) {
            toast_msg('La cantidad no puede ser menor a 1.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_pos') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                cantidad: cantidadEnviar,
                precio: precio
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                toast_msg(r.msg, r.type || 'success');
                load_cart();
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar el producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    $('body').on('change', '.input-update, .input-quantity', function() {
        const $input = $(this);
        const isPrice = $input.hasClass('input-update');
        const precio = isPrice ? $input.val() : $input.data('precio_venta');
        const cantidad = isPrice ? $input.data('cantidad') : $input.val();
        const id = $input.data('id');

        if (String(precio).trim() === '' || String(cantidad).trim() === '') {
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_pos') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad,
                precio: precio
            },
            success: function(r) {
                if (r.status) {
                    load_cart();
                }
                toast_msg(r.msg, r.type || 'success');
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar el producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    try {
        onScan.attachTo(document, {
            suffixKeyCodes: [13],
            onScan: function(barcode) {
                const idalmacen = $('input[name="idalmacenuser"]').val();
                $.ajax({
                    url: "{{ route('admin.add_product_barcode') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: barcode,
                        idalmacen: idalmacen
                    },
                    beforeSend: function() {
                        block_content('#layout-content');
                    },
                    success: function(r) {
                        $('#search-product').val('').focus();
                        close_block('#layout-content');
                        toast_msg(r.msg, r.type || 'success');
                        if (r.status) {
                            load_cart();
                        }
                    },
                    error: function(xhr) {
                        $('#search-product').val('').focus();
                        close_block('#layout-content');
                        toast_msg(xhr.responseJSON?.msg || 'No se pudo agregar el producto por codigo de barras.', xhr.responseJSON?.type || 'error');
                    },
                    dataType: 'json'
                });
            }
        });
    } catch (e) {
        toast_msg('Error de lectura ' + e, 'warning');
    }

    $('body').on('click', '#process-sale', function(event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('pos.open_modal_confirm') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
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

                payModes = r.pay_modes || [];
                saleDocumentTypes = r.document_types || [];
                posTotals = {
                    signo: r.signo || 'S/',
                    subtotal: toNumber(r.subtotal),
                    igv: toNumber(r.igv),
                    total: toNumber(r.total)
                };

                paymentCondition = 'contado';
                fillDocumentTypeOptions(r.default_document_type_id || null);
                resetPaymentRows();
                resetInstallmentRows();
                $('#global-discount').val('0.00');
                load_clients();
                syncCheckoutUI();
                $('#modalConfirmSale').modal('show');
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo abrir el resumen de venta.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-create-client-pos', function(event) {
        event.preventDefault();
        reopenConfirmAfterClientModal = true;
        $('#modalConfirmSale').modal('hide');
    });

    function success_save_client(msg = null, type = null, last_id = null) {
        load_clients(last_id);
    }

    $('#modalClient').on('hidden.bs.modal', function() {
        if (!reopenConfirmAfterClientModal) {
            return;
        }

        reopenConfirmAfterClientModal = false;
        setTimeout(() => {
            $('#modalConfirmSale').modal('show');
        }, 200);
    });

    $('body').on('click', '.doc-choice', function() {
        const code = $(this).data('code');
        const target = saleDocumentTypes.find((item) => String(item.codigo) === String(code));
        if (!target) {
            return;
        }

        $('#select-document-type').val(String(target.id));
        renderClients();
        syncCheckoutUI();
    });

    $('body').on('click', '.payment-condition-choice', function() {
        paymentCondition = String($(this).data('condition'));
        syncCheckoutUI();

        if (paymentCondition === 'credito') {
            const modalBody = document.querySelector('#modalConfirmSale .modal-body');
            const creditBox = document.getElementById('credit-payment-box');

            if (modalBody && creditBox) {
                setTimeout(() => {
                    modalBody.scrollTo({
                        top: creditBox.offsetTop - 18,
                        behavior: 'smooth'
                    });
                }, 120);
            }
        }
    });

    $('body').on('click', '#add-payment-method', function(event) {
        event.preventDefault();
        $('#payment-methods').append(createPaymentRow('', ''));
        $('#payment-methods .remove-payment').removeClass('d-none');
        syncSummary();
    });

    $('body').on('click', '.remove-payment', function() {
        $(this).closest('.payment-row').remove();
        const rows = $('#payment-methods .payment-row');
        if (rows.length === 1) {
            rows.find('.remove-payment').addClass('d-none');
        }
        syncSummary();
    });

    $('body').on('click', '#add-installment', function(event) {
        event.preventDefault();
        $('#installment-methods').append(createInstallmentRow('', getNextInstallmentDate()));
        syncSummary();
    });

    $('body').on('click', '.remove-installment', function() {
        $(this).closest('.installment-row').remove();
        syncSummary();
    });

    $('body').on('change input', '#select-document-type, #select-client, .payment-method, .payment-amount, .installment-amount, .installment-date, #global-discount', function() {
        syncCheckoutUI();
    });

    $('body').on('click', '#confirm-sale', function(event) {
        event.preventDefault();

        const clientId = $('#select-client').val();
        const documentTypeId = $('#select-document-type').val();
        const paymentsPayload = buildPaymentsPayload();
        const installmentsPayload = buildInstallmentsPayload();

        if (!clientId) {
            toast_msg('Debe seleccionar un cliente.', 'warning');
            return;
        }

        if (!documentTypeId) {
            toast_msg('Debe seleccionar el comprobante.', 'warning');
            return;
        }

        if (!isValidClientForDocument()) {
            toast_msg('El cliente no cumple la regla del comprobante seleccionado.', 'warning');
            return;
        }

        if (paymentCondition === 'contado' && paymentsPayload.errorMessage) {
            toast_msg(paymentsPayload.errorMessage, 'warning');
            return;
        }

        if (paymentCondition === 'credito' && installmentsPayload.errorMessage) {
            toast_msg(installmentsPayload.errorMessage, 'warning');
            return;
        }

        if (!canSubmitSale()) {
            toast_msg('Revisa el resumen de la venta antes de confirmar.', 'warning');
            return;
        }

        $('#modalConfirmSale').modal('hide');

        $.ajax({
            url: "{{ route('pos.save_sale') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                client_id: clientId,
                document_type_id: documentTypeId,
                payment_condition: paymentCondition,
                global_discount: toNumber($('#global-discount').val()).toFixed(2),
                payments: paymentCondition === 'contado' ? paymentsPayload.payments : [],
                installments: paymentCondition === 'credito' ? installmentsPayload.installments : []
            },
            dataType: 'json',
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(response) {
                close_block('#layout-content');

                if (!response.status) {
                    toast_msg(response.msg, response.type || 'warning');
                    $('#modalConfirmSale').modal('show');
                    return;
                }

                toast_msg(response.msg || 'Venta registrada correctamente', 'success');
                open_sale_ticket(response.ticket_url);
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo registrar la venta.', xhr.responseJSON?.type || 'error');
                $('#modalConfirmSale').modal('show');
            }
        });
    });

    $(document).ready(function() {
        load_cart();
        $('#search-product').focus();
    });
</script>
