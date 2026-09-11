<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BillingReportExport implements FromView, ShouldAutoSize
{
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows
    ) {
    }

    public function view(): View
    {
        return view('admin.reports.billings.excel', [
            'title' => $this->title,
            'headings' => $this->headings,
            'rows' => $this->rows,
        ]);
    }
}
