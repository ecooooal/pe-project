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

    protected $columnOrder = [
        'id',       
        'exam_id',
        'course_id',
        'subject_id',
        'topic_id',
        'question_id',
        'subject_name',
        'topic_name',
        'question_name',
        'question_type',
        'question_level',
        'question_points',
        'user_id',
        'student_paper_id',
        'attempt',
        'student_name',
        'student_email',
        'course_abbreviation',
        'is_answered',
        'is_correct',
        'points_obtained',
        'first_viewed_at',
        'first_answered_at',
        'last_answered_at',
        'created_at',
        'updated_at' 
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

        $orderedRow = [];
            foreach ($this->columnOrder as $key) {
                $orderedRow[$key] = $row[$key] ?? null; 
            }

        return $orderedRow;
    }

    public function headings(): array
    {
        $report = Report::find($this->reportId);
        
        if (!$report || !is_array($report->raw_report_data) || empty($report->raw_report_data)) {
            return [];
        }

        return $this->columnOrder;
    }
}