<?php

use App\Models\Exam;
use App\Models\User;
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
        Schema::create('student_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Exam::class, 'exam_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('question_count')->nullable();
            $table->json('questions_order')->nullable();
            $table->unsignedSmallInteger('current_position')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'auto_completed'])->default('in_progress')->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('expired_at')->nullable()->index();
            $table->timestamps();
            $table->index(['exam_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_papers');
    }
};
