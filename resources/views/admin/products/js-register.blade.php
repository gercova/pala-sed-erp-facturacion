<script>
    $(document).ready(function() {
        let productSelectsInitialized = false;

        function initProductSelects() {
            if (productSelectsInitialized) {
                return;
            }

            $('#form_save_product .product-select').select2({
                placeholder: '[SELECCIONE]',
                dropdownParent: $('#modalAddProduct'),
                width: '100%'
            });

            productSelectsInitialized = true;
        }

        function resetProductForm() {
            const form = $('#form_save_product');
            form[0].reset();

            $('#idunidad').val($('#idunidad').data('default') || '').trigger('change');
            $('#idcategoria').val($('#idcategoria').data('default') || '').trigger('change');
            $('#idcodigo_igv').val($('#idcodigo_igv').data('default') || '').trigger('change');
            $('#producto').prop('checked', true).trigger('change');
            $('#stock_actual').val(0);
            $('#precio_compra').val('0.00');
            $('#precio_venta').val('0.00');
            $('#codigo_sunat').val('');
        }

        function toggleProductStockSection() {
            const isService = $('input[name="opcion"]:checked').val() === '2';

            if (isService) {
                $('#container-stock').addClass('d-none');
                $('#stock_actual').val(0);
                $('.finance-col').removeClass('col-md-4').addClass('col-md-6');
                return;
            }

            $('.finance-col').removeClass('col-md-6').addClass('col-md-4');
            $('#container-stock').removeClass('d-none');
        }

        function toggleButtonState(button, loading, loadingText = 'Guardando...') {
            const $button = $(button);
            const originalHtml = $button.data('original-html') || $button.html();

            if (! $button.data('original-html')) {
                $button.data('original-html', originalHtml);
            }

            if (loading) {
                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>' + loadingText);
                return;
            }

            $button.prop('disabled', false).html($button.data('original-html'));
        }

        $('body').on('click', '.btn-create-product', function(event) {
            event.preventDefault();
            resetProductForm();
            toggleProductStockSection();
            $('#modalAddProduct').modal('show');
        });

        $('#modalAddProduct').on('shown.bs.modal', function() {
            initProductSelects();
        });

        $('#modalAddProduct').on('hidden.bs.modal', function() {
            resetProductForm();
        });

        $('input[name="opcion"]').on('change', function() {
            toggleProductStockSection();
        });

        $('body').on('click', '.btn-save-product', function(event) {
            event.preventDefault();

            const form = $('#form_save_product');
            const descripcion = form.find('input[name="descripcion"]');
            const precioCompra = form.find('input[name="precio_compra"]');
            const precioVenta = form.find('input[name="precio_venta"]');
            const unidad = form.find('select[name="idunidad"]');
            const categoria = form.find('select[name="idcategoria"]');
            const afectacion = form.find('select[name="idcodigo_igv"]');
            const isService = form.find('input[name="opcion"]:checked').val() === '2';
            const stockActual = form.find('input[name="stock_actual"]');

            descripcion.toggleClass('is-invalid', descripcion.val().trim() === '');
            precioCompra.toggleClass('is-invalid', precioCompra.val().trim() === '');
            precioVenta.toggleClass('is-invalid', precioVenta.val().trim() === '');
            unidad.toggleClass('is-invalid', !unidad.val());
            categoria.toggleClass('is-invalid', !categoria.val());
            afectacion.toggleClass('is-invalid', !afectacion.val());
            stockActual.toggleClass('is-invalid', !isService && stockActual.val().trim() === '');

            if (
                descripcion.val().trim() === '' ||
                precioCompra.val().trim() === '' ||
                precioVenta.val().trim() === '' ||
                !unidad.val() ||
                !categoria.val() ||
                !afectacion.val() ||
                (!isService && stockActual.val().trim() === '')
            ) {
                return;
            }

            const button = this;

            $.ajax({
                url: "{{ route('products.save') }}",
                method: 'POST',
                data: form.serialize(),
                beforeSend: function() {
                    toggleButtonState(button, true);
                },
                success: function(r) {
                    toggleButtonState(button, false);

                    if (!r.status) {
                        toast_msg(r.msg, r.type || 'warning');
                        return;
                    }

                    form.trigger('reset');
                    $('#modalAddProduct').modal('hide');
                    success_save_product(r.msg, r.type);
                },
                error: function(xhr) {
                    toggleButtonState(button, false);
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo guardar el producto.', xhr.responseJSON?.type || 'error');
                },
                dataType: 'json'
            });
        });

        resetProductForm();
        toggleProductStockSection();
    });
</script>
