<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Sheet;

class ProductsExport implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithStyles, WithEvents, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    Private $rowNumber = 1;
    
    public function headings(): array
    {
        return [
            'Sno',
            'Product Name',
            'Product Code',
            'Product Prefix',
            'Product ID',
            'Description',
            'Status',
            'Created At',
        ];
    }

    public function registerEvents(): array
    {
        Writer::macro('setCreator', function (Writer $writer, string $creator) {
            $writer->getDelegate()->getProperties()->setCreator($creator);
        });

        Sheet::macro('setOrientation', function (Sheet $sheet, $orientation) {
            $sheet->getDelegate()->getPageSetup()->setOrientation($orientation);
        });
        
        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });

        return [
            BeforeExport::class  => function(BeforeExport $event) {
                $event->writer->setCreator('Patrick');
            },
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                $event->sheet->styleCells(
                    'A1:H1',
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => 'FF669977'],
                            ],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'color' => ['argb' => 'FF669999']
                        ]
                    ]
                );
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 30,            
            'D' => 20,
            'E' => 25,
            'F' => 30,
            'G' => 20,
            'H' => 25,
        ];
    }

    public function map($product): array
    {
        $description = json_decode($product->description);
        return [
            [
                $this->rowNumber++,
                $product->product_name,
                $product->product_code,
                $product->product_prefix,
                $product->product_id,
                ($description[0]) ? $description[0]->Content[0] : '',
                $this->textCapitilize($product->status),
                ($product->created_at) ? Date::dateTimeToExcel($product->created_at) : '',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY.' '. NumberFormat::FORMAT_DATE_TIME2,
        ];
    }

    private function textCapitilize($str)
    {
        $array = explode('_', $str);

        $array = array_map(function($value) {
            return ucfirst(strtolower($value));
        }, $array);

        return implode(' ', $array);
    }

    public function collection()
    {
        return Product::all();
    }
}
