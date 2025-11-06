<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromArray;
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
use Illuminate\Support\Facades\DB;
use App\Resources\Orders\OrderResource;

class OrdersExport implements FromArray, WithMapping, WithHeadings, WithColumnFormatting, WithStyles, WithEvents, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    Private $rowNumber = 1;
    
    public function headings(): array
    {
        return [
            'Sno',
            'Order Id',
            'Product / Package Name',
            'License Type',
            'Customer Email',
            'Order Date',
            'Source',
            'Order Status',
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
                    'A1:I1',
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
            'B' => 10,
            'C' => 30,            
            'D' => 20,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 25,
            'I' => 25,
        ];
    }

    public function map($order): array
    {
        return [
            [
                $this->rowNumber++,
                @$order['order_id'],
                @$order['product']['product_name'],
                @$order['license_type']['name'],
                @$order['customer']['email'],
                (@$order['order_date']) ? Date::stringToExcel(@$order['order_date']) : '',
                $this->textCapitilize(@$order['source']),
                (@$order['order_status'] != 'FAILED') ? 'Success' : $this->textCapitilize(@$order['order_status']),
                (@$order['created_at']) ? Date::stringToExcel(@$order['created_at']) : '',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY.' '. NumberFormat::FORMAT_DATE_TIME2,
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY.' '. NumberFormat::FORMAT_DATE_TIME2,
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

    public function array(): array
    {
        $result = [];

        $query = new Order;
        $query = $query->orderBy('order_placed_at', 'DESC');
        $orders = $query->get();
        
        $orders->map( function($order) {
            if (!empty($order->customer))
            {
                return $order->customer->makeHidden(['id']);
            }
            else
            {
                return $order->customer;
            }
        });

        $orders->map( function($order) {
            if (!empty($order->orderItems))
            {
                $order->orderItems->map( function($orderItem) {
                    $orderItem->product;
                    $orderItem->licenseType;
                });
            }
        });

        $orders->map( function($order) {
            if (!empty($order->license))
            {
                @$order->license->product;
                @$order->license->customer;
                @$order->license->licenseProduct->licenseType;
                $order->license->makeHidden(['id']);
            }
            else
            {
                @$order->license->product;
                @$order->license->customer;
                @$order->license->licenseProduct->licenseType;
                $order->license;
            }
        });
        
        foreach ($orders as $order)
        {
            if (!empty($order->orderItems) && count($order->orderItems) > 0)
            {
                foreach($order->orderItems as $orderItem) 
                {
                    $result[] = OrderResource::order_details($order, $orderItem);
                }
            }
            else
            {
                $result[] = OrderResource::order_details($order);
            }
        }
        return $result;
    }
}
