<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Str;

class IndividualQuestionPerformanceReport implements FromCollection, WithHeadings, WithStrictNullComparison
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

        if (!$report || !is_array($report->report_data['individual_question_stats'])) {
            return collect();
        }
        $individual_question_stats = collect($report->report_data['individual_question_stats']);
        $individual_question_stats_rows = $individual_question_stats->map(function ($question){
            return [
                'id' => $question['question_id'],
                'name' => $question['question_name'],
                'type' => Str::title(str_replace('_', ' ', $question['question_type'])),
                'level' => Str::ucfirst($question['question_level']),
                'topic' => $question['topic_name'],
                'subject' => $question['subject_name'],
                'points' => $question['question_points'],
                'avg_points' => $question['avg_points_obtained'],
                'student_answers_count' => $question['answered_count'],
                'difficulty_index' => $question['difficulty_index'],
                'discrimination_index' => $question['discrimination_index'],
                'lower_group_percent' => $question['lower_group_percent_correct'],
                'upper_group_percent' => $question['upper_group_percent_correct']
            ];
        });

        return $individual_question_stats_rows;
    }

    public function headings(): array
    {
        $report = Report::find($this->report_id);
        
        if (!$report || !is_array($report->report_data['individual_question_stats']) || empty($report->report_data['individual_question_stats'])) {
            return [];
        }

        return ['Question ID', 'Question Name', 'Type', 'Level', 'Topic' ,'Subject', 'Attainable Points', 'Average Points Obtained', 'Student Answers Count', 'Difficulty Index', 'Discrimination Index', 'Lower Group Percent Correct', 'Upper Group Percent Correct'];
    }
}
