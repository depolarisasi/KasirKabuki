<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new SalesReportSummarySheet($this->reportData, $this->startDate, $this->endDate),
            new TopProductsSheet($this->reportData),
            new CategoryRevenueSheet($this->reportData),
            new DailySalesSheet($this->reportData),
            new PartnerPerformanceSheet($this->reportData),
        ];
    }
} 