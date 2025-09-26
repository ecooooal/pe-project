<?php

use App\Models\Course;
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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('max_score');
            $table->integer('duration')->nullable();
            $table->unsignedTinyInteger('passing_score')->default(50); 
            $table->integer('retakes')->nullable();
            $table->dateTime('examination_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->boolean('is_published',)->default(false);
            $table->string('applied_algorithm')->default('None');
            $table->softDeletes();
            $table->timestamps();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
