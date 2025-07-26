<?php

use App\Models\Question;
use App\Models\StudentPaper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StudentPaper::class, 'student_paper_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class, 'question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('points')->default(0);
            $table->boolean('is_answered')->default(false)->index();
            $table->boolean('is_correct')->default(false)->index();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['student_paper_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
