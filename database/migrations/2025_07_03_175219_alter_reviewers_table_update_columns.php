<?php

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
        Schema::table('reviewers', function (Blueprint $table) {
            // Drop columns you no longer want (adjust to your actual old columns)
            $table->dropColumn(['affiliation', 'name']); 

            // If you’re recreating columns, make sure they exist fresh
            $table->string('topic')->nullable();
            $table->string('author')->nullable();
            // created_at and updated_at are usually already there
        });
    }

    public function down(): void
    {
        Schema::table('reviewers', function (Blueprint $table) {
            $table->dropColumn(['email', 'topic', 'author']);
            // optionally drop timestamps if added
            // $table->dropTimestamps();
        });
    }
};
