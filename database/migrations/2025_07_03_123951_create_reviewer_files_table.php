<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviewer_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id') // automatically UNSIGNED BIGINT
                  ->constrained('reviewers') // points to reviewers.id
                  ->onDelete('cascade');     // cascade delete
            $table->string('topic');
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('reviewer_files');
    }
};
