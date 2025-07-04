<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class StockReportExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $reportData;
    protected $startDate;
    protected $endDate;

    public function __construct($reportData, $startDate, $endDate)
    {
        $this->reportData = $reportData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $summary = $this->reportData['summary'] ?? [];
        $period = $this->reportData['period'] ?? [];
        $dailyReports = $this->reportData['daily_reports'] ?? [];
        
        $data = [
            ['LAPORAN REKONSILIASI STOK SATE BRAGA'],
            [''],
            ['Periode', Carbon::parse($this->startDate)->format('d/m/Y') . ' - ' . Carbon::parse($this->endDate)->format('d/m/Y')],
            ['Dibuat pada', Carbon::now()->format('d/m/Y H:i')],
            [''],
            ['RINGKASAN STOK'],
            ['Total Hari', $summary['total_days'] ?? 0],
            ['Hari dengan Data Stok', $summary['days_with_stock'] ?? 0],
            ['Total Stok Awal', $summary['total_initial_stock'] ?? 0],
            ['Total Terjual', $summary['total_sold'] ?? 0],
            ['Total Stok Akhir', $summary['total_final_stock'] ?? 0],
            ['Total Selisih', $summary['total_differences'] ?? 0],
            ['Rata-rata Stok Harian', round($summary['avg_daily_stock'] ?? 0, 1)],
            ['Rata-rata Terjual Harian', round($summary['avg_daily_sold'] ?? 0, 1)],
            [''],
            ['DETAIL REKONSILIASI HARIAN'],
            ['Tanggal', 'Hari', 'Produk', 'Stok Awal', 'Terjual', 'Stok Akhir', 'Stok Seharusnya', 'Selisih', 'Status'],
        ];

        // Add daily reconciliation details
        foreach ($dailyReports as $day) {
            if ($day['has_data']) {
                foreach ($day['reconciliation'] as $item) {
                    $status = 'Sesuai';
                    if ($item['difference'] > 0) {
                        $status = 'Lebih ' . abs($item['difference']);
                    } elseif ($item['difference'] < 0) {
                        $status = 'Kurang ' . abs($item['difference']);
                    }

                    $data[] = [
                        Carbon::parse($day['date'])->format('d/m/Y'),
                        $day['day_name'],
                        $item['product_name'],
                        $item['initial_stock'],
                        $item['sold'],
                        $item['final_stock'],
                        $item['calculated_stock'],
                        $item['difference'],
                        $status
                    ];
                }
            } else {
                $data[] = [
                    Carbon::parse($day['date'])->format('d/m/Y'),
                    $day['day_name'],
                    'Tidak ada data stok',
                    0,
                    0,
                    0,
                    0,
                    0,
                    '-'
                ];
            }
        }

        // Add daily summary
        $data[] = [''];
        $data[] = ['RINGKASAN HARIAN'];
        $data[] = ['Tanggal', 'Hari', 'Total Stok Awal', 'Total Terjual', 'Total Stok Akhir', 'Total Selisih'];

        foreach ($dailyReports as $day) {
            $data[] = [
                Carbon::parse($day['date'])->format('d/m/Y'),
                $day['day_name'],
                $day['day_totals']['initial_stock'],
                $day['day_totals']['sold'],
                $day['day_totals']['final_stock'],
                $day['day_totals']['difference']
            ];
        }

        return $data;
    }

    public function title(): string
    {
        return 'Laporan Stok';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->array());
        $dailyReports = $this->reportData['daily_reports'] ?? [];
        
        // Calculate where the daily summary starts
        $detailStartRow = 18;
        $detailRows = 0;
        foreach ($dailyReports as $day) {
            if ($day['has_data']) {
                $detailRows += count($day['reconciliation']);
            } else {
                $detailRows += 1;
            }
        }
        
        $summaryStartRow = $detailStartRow + $detailRows + 2;
        
        return [
            // Header title
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']]
            ],
            
            // Section headers
            6 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            16 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            17 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F5E8']]],
            $summaryStartRow => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            ($summaryStartRow + 1) => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFF2CC']]],

            // Number formatting
            'B9:B14' => ['numberFormat' => ['formatCode' => '#,##0']],
            'D' . $detailStartRow . ':H' . ($detailStartRow + $detailRows) => ['numberFormat' => ['formatCode' => '#,##0']],
            'C' . ($summaryStartRow + 2) . ':F' . $lastRow => ['numberFormat' => ['formatCode' => '#,##0']],
        ];
    }
} 