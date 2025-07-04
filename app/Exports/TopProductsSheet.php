<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TopProductsSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function array(): array
    {
        $topProducts = $this->reportData['top_products'] ?? [];
        
        $data = [];
        foreach ($topProducts as $index => $product) {
            $data[] = [
                $index + 1,
                $product['product_name'] ?? '',
                $product['category_name'] ?? '',
                $product['total_quantity'] ?? 0,
                $product['total_revenue'] ?? 0,
                $product['order_count'] ?? 0,
                round($product['avg_quantity_per_order'] ?? 0, 1),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Nama Produk',
            'Kategori',
            'Total Terjual',
            'Total Pendapatan',
            'Jumlah Order',
            'Rata-rata per Order'
        ];
    }

    public function title(): string
    {
        return 'Produk Terlaris';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F5E8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            
            // Currency columns
            'E:E' => ['numberFormat' => ['formatCode' => '#,##0']],
            'G:G' => ['numberFormat' => ['formatCode' => '0.0']],
            
            // Number columns
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'D:D' => ['numberFormat' => ['formatCode' => '#,##0']],
            'F:F' => ['numberFormat' => ['formatCode' => '#,##0']],
        ];
    }
} 