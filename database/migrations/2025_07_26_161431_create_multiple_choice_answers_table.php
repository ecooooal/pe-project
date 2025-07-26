<?php

use App\Models\StudentAnswer;
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
        Schema::create('multiple_choice_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StudentAnswer::class, 'student_answer_id')->constrained()->cascadeOnDelete();
            $table->char('answer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiple_choice_answers');
    }
};
