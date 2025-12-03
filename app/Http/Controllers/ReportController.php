<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Report;
use App\Services\ExamService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Str;

class ReportController extends Controller
{
    protected $userService;
    protected $examService;

    public function __construct(UserService $userService, ExamService $examService)
    {
        $this->userService = $userService;
        $this->examService = $examService;
    }

    public function index(){
        $courseIds = $this->userService->getCoursesForUser(auth()->user())->pluck('id');
        $exams = Exam::with(['courses', 'questions'])
            ->whereHas('courses', function ($query) use ($courseIds) {
                $query->whereIn('courses.id', $courseIds);
            });

        $query = QueryBuilder::for($exams)
            ->allowedFilters([
                'name'
            ])
            ->paginate(10)
            ->appends(request()->query());

        $data = [
            'exams' => $query
        ];
        return view('reports/index', $data);

    }

    public function index_exam(Exam $exam){
        $exam->load('courses');
        $exam->load(['reports' => function ($query) {
            $query->select(
                'id', 
                'exam_id',
                'course_count', 
                'subject_count',
                'topic_count',
                'question_count',
                'student_count',
                'created_at'
            );
        }]);
        $reports = $exam->reports;
    
        return view('reports/index_exam', ['exam' => $exam, 'reports' => $reports]);

    }

    public function show(Exam $exam, Report $report){
        $report_data = $report->report_data;
        $overview_data = $report_data['exam_overview_data'];


        $exam_name = $exam->name;
        $created_date = $report->created_at;
        $courses =  $overview_data['courses'];
        $subject_count = $overview_data['subject_count'];
        $topic_count = $overview_data['topic_count'];
        $question_count = $overview_data['question_count'];
        $student_count = $overview_data['student_count'];
        $question_levels = $overview_data['questions_levels'];

        $student_statuses_data = $report_data['student_statuses_data'] ?? [];
        $more_review_count = $student_statuses_data['more_review'] ?? 0;
        $pass_count = $student_statuses_data['pass'] ?? 0;
        $perfect_score_count = $student_statuses_data['perfect_score'] ?? 0;
        $passing_rate = $student_count > 0
            ? round((($pass_count + $perfect_score_count) / $student_count) * 100)
            : 0;

        $exam_summary_data = $report_data['exam_summary_data'];
        $question_levels_summary_data = $report_data['question_levels_summary_data'];
        $subjects_min_max_data = $report_data['subjects_min_max_data'];
        $exam_histogram_boxplot_data = $report_data['exam_histogram_boxplot_data'];
        $normalized_exam_scores_by_subjects = $report_data['normalized_exam_scores_by_subjects'];
        $normalized_exam_scores_by_topics = $report_data['normalized_exam_scores_by_topics'];
        $exam_by_types_with_levels = $report_data['exam_by_types_with_levels'];
        $exam_question_heatstrip = $report_data['exam_question_heatstrip'];

        $individual_question_stats = collect($report_data['individual_question_stats']);
        $individual_question_stats_headers = ['Question Name', 'Type', 'Level', 'Topic' ,'Subject', 'Points', 'Average Points Obtained', 'Student Answers Count', 'Difficulty Index', 'Discrimination Index', 'Lower Group Percent Correct', 'Uppper Group Percent Correct'];
        $individual_question_stats_rows = $individual_question_stats->map(function ($question){
            return [
                'id' => $question['question_id'],
                'name' => $question['question_name'],
                'type' => $question['question_type'],
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
        $individual_student_performance = collect($report_data['individual_student_performance']);
        $individual_student_performance_headers = ['Name', 'Email', 'Course', 'Attempt Count', 'Total Score', 'No. of Answered Correct', 'Exam Accuracy', 'Remember Accuracy', 'Understand Accuracy', 'Apply Accuracy', 'Analyze Accuracy', 'Evaluate Accuracy', 'Create Accuracy'];
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
                'remember_accuracy' => $student['remember_accuracy'] ?? "Null",
                'understand_accuracy' => $student['understand_accuracy'] ?? "Null",
                'apply_accuracy' => $student['apply_accuracy'] ?? "Null",
                'analyze_accuracy' => $student['analyze_accuracy'] ?? "Null",
                'evaluate_accuracy' => $student['evaluate_accuracy'] ?? "Null",
                'create_accuracy' => $student['create_accuracy'] ?? "Null"
            ];
        });
        
        $data = [
            'exam' => $exam->id,
            'report' => $report->id,
            // metadata
            'exam_name' => $exam_name,
            'created_date' => $created_date,
            'courses' => $courses,
            'blooms_level_contained' => $question_levels,
            // data overview
            'students_count' => $student_count,
            'subjects_count' => $subject_count,
            'topics_count' => $topic_count,
            'questions_count' => $question_count,
            'max_score_range' => $exam_summary_data['exam_maximum_score'],
            'more_review_count' => $more_review_count,
            'pass_count' => $pass_count,
            'perfect_score_count' => $perfect_score_count,
            'passing_rate' => $passing_rate,
            // descriptive statistics
            'mean' => $exam_summary_data['mean'],
            'median' => $exam_summary_data['median'],
            'mode' => $exam_summary_data['mode'],
            'max' => $exam_summary_data['max'],
            'min' => $exam_summary_data['min'],
            'range' => $exam_summary_data['range'],
            'std_dev' => $exam_summary_data['standard_deviation'] ?? 0,
            'question_level_summary' => $question_levels_summary_data,
            'subjects_three_min' => $subjects_min_max_data['top_three_min_subjects'],
            'subjects_three_max' => $subjects_min_max_data['top_three_max_subjects'],
            // Exam Scores
            'exam_histogram_boxplot_data' => $exam_histogram_boxplot_data,
            'normalized_exam_scores_by_subjects_data' => $normalized_exam_scores_by_subjects,
            'normalized_exam_scores_by_topics_data' => $normalized_exam_scores_by_topics,
            'exam_question_heatstrip_data' => $exam_question_heatstrip,
            'exam_compare_types_and_blooms_data' =>$exam_by_types_with_levels,
            // Individual performance analysis
            'individual_question_stats_headers' => $individual_question_stats_headers,
            'individual_question_stats_rows' => $individual_question_stats_rows,
            'individual_student_performance_headers' => $individual_student_performance_headers,
            'individual_student_performance_rows' => $individual_student_performance_rows
        ];
        return view('reports/show', $data);
    }

    public function create(Exam $exam){
        $topics = $this->examService->getTopicsForExam($exam)->count();
        $subjects = $this->examService->getSubjectsForExam($exam)->count();
        $questions =  $exam->questions()->count();
        $enrolled_students = $exam->users()->count();
        $students_that_took_exam = $exam->takers()->count();
        $data = [
            'exam' => $exam,
            'subjects' => $subjects,
            'topics' => $topics,
            'questions' =>$questions,
            'students' => $enrolled_students,
            'takers' => $students_that_took_exam
        ];

        return view('reports/create', $data);
    }

    public function store(Exam $exam){
        $response = Http::timeout(30)
            ->get("http://fastapi:8080/api/reports/create-store/{$exam->id}");

        if (!$response->successful()) {

            session()->flash('toast', json_encode([
                'status' => 'Error!',
                'message' => 'Create Report Unsuccessful, CHECK FASTAPI',
                'type' => 'error'
            ]));

            return response('', 200)->header('HX-Redirect', route('reports.index_exam', ['exam' => $exam]));
        }

        $report_data = $response->json('exam_performance');
        $raw_report_data = $response->json('raw_exam_performance');

        $exam_overview_data = $report_data['exam_overview_data'];
        $course_count = $exam_overview_data['course_count'];
        $subject_count = $exam_overview_data['subject_count'];
        $topic_count = $exam_overview_data['topic_count'];
        $question_count = $exam_overview_data['question_count'];
        $student_count = $exam_overview_data['student_count'];

        $report = $exam->reports()->create([
            'course_count' => $course_count,
            'subject_count' => $subject_count,
            'topic_count' => $topic_count,
            'question_count' => $question_count,
            'student_count' => $student_count,
            'report_data' => $report_data,
            'raw_report_data' =>  $raw_report_data
        ]);



        session()->flash('toast', json_encode([
            'status' => 'Created!',
            'message' => 'Report for: ' . $exam->name,
            'type' => 'success'
        ]));

        return response('', 200)->header('HX-Redirect', route('reports.show', ['exam' => $exam, 'report' => $report]));
    }

    public function destroy(Exam $exam, Report $report){
        session()->flash('toast', json_encode([
            'status' => 'Destroyed!',
            'message' => 'Report for: ' . $report->created_at,
            'type' => 'warning'
        ]));

        $report->delete();

        return response('', 200)->header('HX-Redirect', route('reports.index_exam', ['exam' => $exam]));
    }

    public function exportReport(Exam $exam, Report $report){
        if ($report->deleted_at != null){
            return response()->json(['error' => "This report has been archived"], 401);
        }
    
        if ($report->exam_id != $exam->id){
            return response()->json(['error' => "Mismatch exam and report"], 401);
        }

        $filename = $exam->name. '_' . $report->created_at->format('Y-m-d') . '.json';

        // Fetch all reports, get only raw_json_data
        $reports = DB::table('reports')->where('id', '=', $report->id)->Value('raw_report_data');
        return response($reports,
        200,
        [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]
    );

    }
    
}
