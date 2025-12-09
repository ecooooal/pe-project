<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Illuminate\Support\Collection;

class RawReportExport implements FromCollection, WithHeadings
{
    use Exportable;
    protected $reportId;
    public function __construct(int $report_id)
    {
        $this->reportId = $report_id;
    }
    public function collection(): Collection
    {
        $report = Report::find($this->reportId);

        if (!$report || !is_array($report->raw_report_data)) {
            return collect();
        }

        return collect($report->raw_report_data);
    }

    public function headings(): array
    {
        $report = Report::find($this->reportId);
        
        if (!$report || !is_array($report->raw_report_data) || empty($report->raw_report_data)) {
            return [];
        }

        $firstRow = $report->raw_report_data[0];

        if (is_array($firstRow)) {
            return array_keys($firstRow);
        }
        
        return [];
    }
}