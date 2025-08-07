<?php

use App\Models\ExamRecord;
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
        Schema::create('exam_records_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExamRecord::class, 'exam_record_id')->constrained()->cascadeOnDelete()->index();
            $table->unsignedBigInteger('subject_id'); 
            $table->string('subject_name'); 
            $table->unsignedSmallInteger('score_obtained');
            $table->unsignedSmallInteger('score');
            $table->timestamps();

            $table->unique(['exam_record_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_records_subjects');
    }
};
