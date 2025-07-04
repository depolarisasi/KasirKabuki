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

class ExpenseReportExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize
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
        $allExpenses = $this->reportData['all_expenses'] ?? [];
        
        $data = [
            ['LAPORAN PENGELUARAN SATE BRAGA'],
            [''],
            ['Periode', Carbon::parse($this->startDate)->format('d/m/Y') . ' - ' . Carbon::parse($this->endDate)->format('d/m/Y')],
            ['Dibuat pada', Carbon::now()->format('d/m/Y H:i')],
            [''],
            ['RINGKASAN PENGELUARAN'],
            ['Total Pengeluaran', $summary['total_expenses'] ?? 0],
            ['Jumlah Transaksi', $summary['expense_count'] ?? 0],
            ['Rata-rata per Transaksi', $summary['average_expense'] ?? 0],
            [''],
            ['DETAIL PENGELUARAN'],
            ['Tanggal', 'Waktu', 'Keterangan', 'Jumlah', 'Diinput oleh'],
        ];

        // Add expense details
        foreach ($allExpenses as $expense) {
            $data[] = [
                Carbon::parse($expense['date'])->format('d/m/Y'),
                Carbon::parse($expense['created_at'])->format('H:i'),
                $expense['description'],
                $expense['amount'],
                $expense['user']['name'] ?? 'Unknown'
            ];
        }

        // Add summary by date
        $data[] = [''];
        $data[] = ['RINGKASAN PER TANGGAL'];
        $data[] = ['Tanggal', 'Jumlah Transaksi', 'Total Pengeluaran'];

        $expensesByDate = $this->reportData['expenses_by_date'] ?? [];
        foreach ($expensesByDate as $date => $dayData) {
            $data[] = [
                Carbon::parse($date)->format('d/m/Y'),
                $dayData['count'],
                $dayData['total']
            ];
        }

        return $data;
    }

    public function title(): string
    {
        return 'Laporan Pengeluaran';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->array());
        
        return [
            // Header title
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE6E6']]
            ],
            
            // Section headers
            6 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            11 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']]],
            12 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F5E8']]],

            // Currency formatting for amount columns
            'B7:B9' => ['numberFormat' => ['formatCode' => '#,##0']],
            'D13:D' . $lastRow => ['numberFormat' => ['formatCode' => '#,##0']],
            
            // Find summary by date section and format
            'C' . ($lastRow - count($this->reportData['expenses_by_date'] ?? []) + 1) . ':C' . $lastRow => [
                'numberFormat' => ['formatCode' => '#,##0']
            ],
        ];
    }
} 