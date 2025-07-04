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

class PartnerPerformanceSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function array(): array
    {
        $partnerPerformance = $this->reportData['partner_performance'] ?? [];
        
        $data = [];
        foreach ($partnerPerformance as $partner) {
            $data[] = [
                $partner['partner_name'] ?? '',
                $partner['commission_rate'] ?? 0,
                $partner['order_count'] ?? 0,
                $partner['gross_revenue'] ?? 0,
                $partner['total_commission'] ?? 0,
                $partner['net_revenue'] ?? 0,
                $partner['avg_order_value'] ?? 0,
            ];
        }

        // Add total row if there's data
        if (!empty($partnerPerformance)) {
            $totalOrders = collect($partnerPerformance)->sum('order_count');
            $totalGrossRevenue = collect($partnerPerformance)->sum('gross_revenue');
            $totalCommission = collect($partnerPerformance)->sum('total_commission');
            $totalNetRevenue = collect($partnerPerformance)->sum('net_revenue');
            $avgOrderValue = $totalOrders > 0 ? $totalGrossRevenue / $totalOrders : 0;

            $data[] = []; // Empty row
            $data[] = [
                'TOTAL',
                '',
                $totalOrders,
                $totalGrossRevenue,
                $totalCommission,
                $totalNetRevenue,
                $avgOrderValue,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Partner',
            'Commission Rate (%)',
            'Jumlah Order',
            'Pendapatan Kotor',
            'Total Komisi',
            'Pendapatan Bersih',
            'Rata-rata Order'
        ];
    }

    public function title(): string
    {
        return 'Performa Partner';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->array()) + 1; // +1 for header
        
        return [
            // Header row
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFF2CC']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            
            // Total row
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F0F0']],
            ],
            
            // Currency columns
            'D:G' => ['numberFormat' => ['formatCode' => '#,##0']],
            
            // Percentage column
            'B:B' => ['numberFormat' => ['formatCode' => '0.0"%"']],
            
            // Number columns
            'C:C' => ['numberFormat' => ['formatCode' => '#,##0']],
        ];
    }
} 