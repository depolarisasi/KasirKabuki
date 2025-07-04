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
use Carbon\Carbon;

class DailySalesSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function array(): array
    {
        $dailySales = $this->reportData['daily_sales'] ?? [];
        
        $data = [];
        foreach ($dailySales as $day) {
            $data[] = [
                Carbon::parse($day['date'])->format('d/m/Y'),
                Carbon::parse($day['date'])->format('l'), // Day name
                $day['transaction_count'] ?? 0,
                $day['gross_revenue'] ?? 0,
                $day['total_discount'] ?? 0,
                $day['net_revenue'] ?? 0,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'Jumlah Transaksi',
            'Pendapatan Kotor',
            'Total Diskon',
            'Pendapatan Bersih'
        ];
    }

    public function title(): string
    {
        return 'Penjualan Harian';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E1F5FE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            
            // Currency columns
            'D:F' => ['numberFormat' => ['formatCode' => '#,##0']],
            
            // Number columns
            'C:C' => ['numberFormat' => ['formatCode' => '#,##0']],
            
            // Date columns
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'B:B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
} 