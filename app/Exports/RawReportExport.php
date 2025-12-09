<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class RawReportExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;
    protected $reportId;
    protected $columnTypes = [
        'is_correct' => 'boolean',
        'is_answered' => 'boolean',
        'points_obtained' => 'integer',
    ];

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

    public function map($row): array
    {
        $booleanColumns = array_keys(array_filter($this->columnTypes, function($type) {
            return $type === 'boolean';
        }));
        
        foreach ($booleanColumns as $column) {
            if (isset($row[$column])) {
                $row[$column] = $row[$column] ? 'TRUE' : 'FALSE';
            }
        }

        return $row;
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