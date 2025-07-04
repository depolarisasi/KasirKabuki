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

class CategoryRevenueSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function array(): array
    {
        $categoryRevenue = $this->reportData['revenue_by_category'] ?? [];
        
        $data = [];
        foreach ($categoryRevenue as $category) {
            $data[] = [
                $category['category_name'] ?? '',
                $category['total_quantity'] ?? 0,
                $category['total_revenue'] ?? 0,
                $category['order_count'] ?? 0,
                round($category['percentage'] ?? 0, 1),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Total Quantity',
            'Total Pendapatan',
            'Jumlah Order',
            'Persentase (%)'
        ];
    }

    public function title(): string
    {
        return 'Pendapatan per Kategori';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFF3CD']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            
            // Currency columns
            'C:C' => ['numberFormat' => ['formatCode' => '#,##0']],
            
            // Number columns
            'B:B' => ['numberFormat' => ['formatCode' => '#,##0']],
            'D:D' => ['numberFormat' => ['formatCode' => '#,##0']],
            'E:E' => ['numberFormat' => ['formatCode' => '0.0"%"']],
        ];
    }
} 