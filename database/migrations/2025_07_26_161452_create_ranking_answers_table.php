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
        Schema::create('ranking_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StudentAnswer::class, 'student_answer_id')->constrained()->cascadeOnDelete();
            $table->string('answer');
            $table->integer('answer_order');
            $table->unsignedTinyInteger('answer_points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_answers');
    }
};
