<script>
    $(document).ready(function () {
        const tableSelector = @json($tableId);
        const dataUrl = @json($dataRoute);
        const pdfUrl = @json($pdfRoute);
        const excelUrl = @json($excelRoute);
        const columns = @json($datatableColumns);
        const currencySign = @json($signo);

        function formatMoney(value) {
            return `${currencySign} ${Number(value || 0).toFixed(2)}`;
        }

        function applySummary(summary) {
            if (!summary) {
                return;
            }

            $('#summary_count').text(summary.count ?? 0);
            $('#summary_gravada').text(formatMoney(summary.gravada));
            $('#summary_igv').text(formatMoney(summary.igv));
            $('#summary_total').text(formatMoney(summary.total));
        }

        function toggleExportButton($button, loading, text) {
            if (loading) {
                $button.data('original-text', $button.find('.report-export-label').text());
                $button.find('.report-export-label').text(text);
                $button.addClass('btn-loading-active').prop('disabled', true);
                return;
            }

            $button.find('.report-export-label').text($button.data('original-text'));
            $button.removeClass('btn-loading-active').prop('disabled', false);
        }

        function filterPayload() {
            return {
                filter_date_from: $('#filter_date_from').val(),
                filter_date_to: $('#filter_date_to').val(),
                filter_document_type: $('#filter_document_type').length ? $('#filter_document_type').val() : '',
                filter_status: $('#filter_status').length ? $('#filter_status').val() : '',
                filter_sunat_status: $('#filter_sunat_status').length ? $('#filter_sunat_status').val() : '',
                filter_pay_mode: $('#filter_pay_mode').length ? $('#filter_pay_mode').val() : '',
                filter_client_id: $('#filter_client_id').length ? $('#filter_client_id').val() : '',
                filter_series: $('#filter_series').length ? $('#filter_series').val() : '',
                filter_user: $('#filter_user').length ? $('#filter_user').val() : ''
            };
        }

        function buildExportUrl(baseUrl) {
            return baseUrl + '?' + $.param(filterPayload());
        }

        async function triggerDownload($button, url, loadingText) {
            toggleExportButton($button, true, loadingText);

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const blob = await response.blob();
                const disposition = response.headers.get('Content-Disposition') || '';
                const match = disposition.match(/filename\\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i);
                const filename = decodeURIComponent((match && (match[1] || match[2])) || 'reporte');
                const objectUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = objectUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();

                setTimeout(function () {
                    window.URL.revokeObjectURL(objectUrl);
                }, 1000);
            } catch (error) {
                console.error('No se pudo descargar el reporte.', error);
                window.location.href = url;
            } finally {
                toggleExportButton($button, false, '');
            }
        }

        if ($('#filter_client_id').length) {
            $('#filter_client_id').select2({
                width: '100%',
                placeholder: @json($clientPlaceholder ?? 'Documento - nombre del cliente'),
                allowClear: false
            });
        }

        const table = $(tableSelector).DataTable({
            processing: false,
            serverSide: true,
            paging: true,
            searching: false,
            destroy: true,
            responsive: false,
            ordering: false,
            autoWidth: false,
            scrollX: true,
            bFilter: false,
            sDom: 'tlpi',
            lengthMenu: [
                [15, 30, 50, -1],
                [15, 30, 50, 'Todos']
            ],
            ajax: {
                url: dataUrl,
                data: function (d) {
                    Object.assign(d, filterPayload());
                }
            },
            columns: columns,
            language: {
                decimal: '',
                emptyTable: 'No hay registros para el rango seleccionado.',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                lengthMenu: 'Mostrar _MENU_ por pagina',
                loadingRecords: 'Cargando reporte...',
                processing: '',
                search: '',
                zeroRecords: 'No encontramos resultados con esos filtros.',
                paginate: {
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            drawCallback: function () {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });

        $(tableSelector).on('xhr.dt', function (e, settings, json) {
            applySummary(json?.summary);
        });

        let filterTimer;
        $('#filter_date_from, #filter_date_to, #filter_document_type, #filter_status, #filter_sunat_status, #filter_pay_mode, #filter_client_id, #filter_series, #filter_user')
            .on('input change', function () {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function () {
                    table.ajax.reload();
                }, 260);
            });

        $('#btn_reset_filters').on('click', function () {
            $('#filter_date_from').val(@json($defaultDateFrom));
            $('#filter_date_to').val(@json($defaultDateTo));
            $('#filter_document_type').val('');
            $('#filter_status').val('');
            $('#filter_sunat_status').val('');
            $('#filter_pay_mode').val('');
            $('#filter_client_id').val('').trigger('change.select2');
            $('#filter_series').val('');
            $('#filter_user').val('');
            table.ajax.reload();
        });

        $('#btn_export_pdf').on('click', function () {
            triggerDownload($(this), buildExportUrl(pdfUrl), 'Descargando PDF...');
        });

        $('#btn_export_excel').on('click', function () {
            triggerDownload($(this), buildExportUrl(excelUrl), 'Generando Excel...');
        });
    });
</script>
