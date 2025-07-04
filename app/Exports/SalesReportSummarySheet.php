<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class SalesReportSummarySheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
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
        
        return [
            ['LAPORAN PENJUALAN SATE BRAGA'],
            [''],
            ['Periode', Carbon::parse($this->startDate)->format('d/m/Y') . ' - ' . Carbon::parse($this->endDate)->format('d/m/Y')],
            ['Dibuat pada', Carbon::now()->format('d/m/Y H:i')],
            [''],
            ['RINGKASAN PENJUALAN'],
            ['Total Transaksi', $summary['total_transactions'] ?? 0],
            ['Total Item Terjual', $this->getTotalItemsSold()],
            [''],
            ['PENDAPATAN'],
            ['Pendapatan Kotor', $summary['total_gross_revenue'] ?? 0],
            ['Total Diskon', '-' . ($summary['total_discounts'] ?? 0)],
            ['Komisi Partner', '-' . ($summary['total_commissions'] ?? 0)],
            ['Pendapatan Bersih', $summary['total_net_revenue'] ?? 0],
            [''],
            ['PENGELUARAN & KEUNTUNGAN'],
            ['Pendapatan Bersih', $summary['total_net_revenue'] ?? 0],
            ['Total Pengeluaran', '-' . ($summary['total_expenses'] ?? 0)],
            ['Keuntungan Bersih', $summary['net_profit'] ?? 0],
            [''],
            ['RATA-RATA'],
            ['Nilai Order Rata-rata', $summary['avg_order_value'] ?? 0],
            ['Transaksi per Hari', $this->getAvgTransactionsPerDay()],
            [''],
            ['BREAKDOWN JENIS PESANAN'],
            ['Jenis Pesanan', 'Jumlah', 'Pendapatan Kotor', 'Pendapatan Bersih', 'Rata-rata Order'],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    protected function getTotalItemsSold()
    {
        $topProducts = $this->reportData['top_products'] ?? [];
        return collect($topProducts)->sum('total_quantity');
    }

    protected function getAvgTransactionsPerDay()
    {
        $totalTransactions = $this->reportData['summary']['total_transactions'] ?? 0;
        $daysCount = $this->reportData['period']['days_count'] ?? 1;
        return $daysCount > 0 ? round($totalTransactions / $daysCount, 1) : 0;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header title
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E3F2FD']]
            ],
            
            // Section headers
            6 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            10 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            16 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            21 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            25 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            26 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F5E8']]],

            // Currency formatting
            'B7:B23' => ['numberFormat' => ['formatCode' => '#,##0']],
        ];
    }
} 