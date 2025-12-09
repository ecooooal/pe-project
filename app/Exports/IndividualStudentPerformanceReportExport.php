<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Illuminate\Support\Collection;

class IndividualStudentPerformanceReportExport implements FromCollection, WithHeadings
{
    use Exportable;
    protected $report_id;
    public function __construct(int $report_id)
    {
        $this->report_id = $report_id;
    }
    public function collection(): Collection
    {
        $report = Report::find($this->report_id);

        if (!$report || !is_array($report->report_data['individual_student_performance'])) {
            return collect();
        }
        $individual_student_performance = collect($report->report_data['individual_student_performance']);
        $individual_student_performance_rows = $individual_student_performance->map(function ($student){
            return [
                'id' => $student['user_id'],
                'name' => $student['student_name'],
                'email' => $student['student_email'],
                'course' => $student['course_abbreviation'],
                'attempt' => $student['attempt'],
                'total_score' => $student['total_score'],
                'no_answered_correct' => $student['correct_count'],
                'exam_accuracy' => $student['exam_accuracy'],
                'remember_accuracy' => $student['remember_accuracy'],
                'understand_accuracy' => $student['understand_accuracy'],
                'apply_accuracy' => $student['apply_accuracy'],
                'analyze_accuracy' => $student['analyze_accuracy'],
                'evaluate_accuracy' => $student['evaluate_accuracy'],
                'create_accuracy' => $student['create_accuracy']
            ];
        });

        return $individual_student_performance_rows;
    }

    public function headings(): array
    {
        $report = Report::find($this->report_id);
        
        if (!$report || !is_array($report->report_data['individual_student_performance']) || empty($report->report_data['individual_student_performance'])) {
            return [];
        }

        return ['Student ID','Name', 'Email', 'Course', 'Attempt Count', 'Total Score', 'No. of Answered Correct', 'Exam Accuracy', 'Remember Accuracy', 'Understand Accuracy', 'Apply Accuracy', 'Analyze Accuracy', 'Evaluate Accuracy', 'Create Accuracy'];
    }
}
