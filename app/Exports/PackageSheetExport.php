<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
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
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Sheet;

class PackageSheetExport implements FromArray, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;
    protected $rowNumber = 1;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Sno',
            'Username',
            'Email',
            'Phone',
            'License Type',
            'Purchased Package',
            'Purchased Date',
            'Activated On',
            'Expiry Date',
            'Status'
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
            BeforeExport::class  => function (BeforeExport $event) {
                $event->writer->setCreator('Patrick');
            },
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                $event->sheet->styleCells(
                    'A1:J1',
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

    public function map($list): array
    {
        return [
            $this->rowNumber++,
            @$list['customer']['first_name'],
            @$list['customer']['email'],
            @$list['customer']['phone'] ?? '-',
            @$list['license_type'] ? $this->textCapitilize(@$list['license_type']) : "-",
            @$list['package']['package_name'] ?? '-',
            @$list['order']['order_placed_at'] ? Date::stringToExcel(AppHelper::convert_user_date_timezone(@$list['order']['order_placed_at'])) : '',
            @$list['purchased_date'] ? Date::stringToExcel(AppHelper::convert_user_date_timezone(@$list['purchased_date'])) : '',
            @$list['expiry_date'] ? Date::stringToExcel(AppHelper::convert_user_date_timezone(@$list['expiry_date'])) : '',
            @$list['status'] ? $this->textCapitilize(@$list['status']) : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,// . ' ' . NumberFormat::FORMAT_DATE_TIME2,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,// . ' ' . NumberFormat::FORMAT_DATE_TIME2,
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,// . ' ' . NumberFormat::FORMAT_DATE_TIME2,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 10,
            'C' => 20,
            'D' => 30,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 25,
            'I' => 25,
            'J' => 15,
        ];
    }

    private function textCapitilize($str)
    {
        $array = explode('_', $str);

        $array = array_map(function ($value) {
            return ucfirst(strtolower($value));
        }, $array);

        return implode(' ', $array);
    }

    public function array(): array
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'Packages';
    }
}
