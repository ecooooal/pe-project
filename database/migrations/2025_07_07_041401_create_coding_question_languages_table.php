<?php

use App\Models\CodingQuestion;
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
        Schema::create('coding_question_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CodingQuestion::class, 'coding_question_id')->constrained();
            $table->string('language'); 
            $table->string('complete_solution_file');
            $table->string('initial_solution_file');
            $table->string('test_case_file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coding_question_languages');
    }
};
