<?php

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
        Schema::create('exam_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(StudentPaper::class, 'student_paper_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt');
            $table->unsignedSmallInteger('total_score');
            $table->timestamp('date_taken')->nullable();
            $table->integer('time_taken')->nullable();
            $table->enum('status', [
                'in_progress',
                'pass',
                'more_review',
                'perfect_score'
            ])->default('in_progress')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_records');
    }
};
