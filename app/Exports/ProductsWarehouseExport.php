<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents; // Asegúrate de importar esta clase
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class ProductsWarehouseExport implements FromView, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('admin.warehouses.products.format_excel', [
            'productos' => $this->data
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Crea un objeto RichText
                $richText = new RichText();
                $richText->createText('Modifique todos los campos, excepto la descripción.');

                // Ajusta el comentario en la celda A1 o en la celda que desees
                $event->sheet->getDelegate()->getComment('A1')
                    ->setAuthor('Sistema')
                    ->setText($richText);
            },
        ];
    }
}
