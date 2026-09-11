<style>
    .billing-report-card,
    .billing-report-panel {
        border: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
    }

    .billing-report-subtitle {
        color: var(--bs-secondary-color);
        font-size: .92rem;
        line-height: 1.45;
        max-width: 760px;
    }

    .billing-report-filters .form-control,
    .billing-report-filters .form-select {
        min-height: 42px;
    }

    .billing-report-client-select + .select2-container {
        width: 100% !important;
    }

    .billing-report-client-select + .select2-container .select2-selection--single {
        align-items: center;
        border: 1px solid var(--bs-border-color);
        border-radius: .5rem;
        display: flex;
        height: 42px;
        min-height: 42px;
    }

    .billing-report-client-select + .select2-container .select2-selection__rendered {
        line-height: 40px !important;
        padding-left: 14px !important;
        padding-right: 40px !important;
    }

    .billing-report-client-select + .select2-container .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }

    .billing-report-client-select + .select2-container .select2-selection__clear {
        display: none !important;
    }

    .billing-report-clear-btn {
        background: var(--bs-light);
        border: 1px solid var(--bs-border-color);
        color: var(--bs-body-color);
        height: 42px;
        min-height: 42px;
        width: 152px;
        max-width: 100%;
        padding: 0 14px;
    }

    .billing-report-clear-btn:hover {
        background: var(--bs-secondary-bg);
        color: var(--bs-body-color);
    }

    .report-doc-cell,
    .report-client-cell {
        line-height: 1.2;
    }

    .report-doc-code,
    .report-client-name {
        color: var(--bs-body-color);
        font-weight: 600;
    }

    .report-doc-type,
    .report-client-doc {
        color: var(--bs-secondary-color);
        font-size: .79rem;
    }

    .report-chip,
    .report-total-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--bs-tertiary-bg);
        min-height: 30px;
        padding: 6px 10px;
        font-weight: 700;
        min-width: 88px;
    }

    .report-total-chip {
        min-width: 118px;
    }

    .report-table {
        font-size: .86rem;
    }

    .report-table thead th {
        font-size: .77rem;
        padding: 10px 8px;
    }

    .report-table tbody td {
        font-size: .82rem;
        padding: 8px 8px;
        vertical-align: middle;
    }

    .report-table_wrapper .dataTables_length {
        padding: 20px 0 0;
        float: left;
    }

    .report-table_wrapper .dataTables_info,
    .report-table_wrapper .dataTables_paginate {
        margin-top: 8px;
    }

    .report-export-btn {
        height: 42px;
        min-width: 152px;
    }

    .report-export-btn.btn-loading-active {
        color: #fff !important;
    }

    #btn_export_pdf.btn-loading-active {
        background: var(--bs-danger) !important;
        border-color: var(--bs-danger) !important;
    }

    #btn_export_excel.btn-loading-active {
        background: var(--bs-success) !important;
        border-color: var(--bs-success) !important;
    }

    .report-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 1rem;
    }

    .report-summary-item {
        border: 1px solid var(--bs-border-color);
        background: #fff;
        border-radius: 18px;
        padding: 14px 16px;
    }

    .report-summary-item small {
        display: block;
        color: var(--bs-secondary-color);
        margin-bottom: 4px;
    }

    .report-summary-item strong {
        font-size: 1.25rem;
        color: var(--bs-body-color);
    }
</style>
