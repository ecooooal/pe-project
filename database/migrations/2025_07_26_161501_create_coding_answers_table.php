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
        Schema::create('coding_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StudentAnswer::class, 'student_answer_id')->constrained()->cascadeOnDelete();
            $table->string('answer_language'); 
            $table->string('answer_file_path');
            $table->enum('status', [
                'pending',
                'checked',
                'failed',
            ])->default('pending')->index();
            $table->unsignedTinyInteger('answer_syntax_points')->default(0);
            $table->unsignedTinyInteger('answer_runtime_points')->default(0);
            $table->unsignedTinyInteger('answer_test_case_points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coding_answers');
    }
};
