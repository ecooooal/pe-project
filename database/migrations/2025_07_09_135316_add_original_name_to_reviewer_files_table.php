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
        Schema::table('reviewer_files', function (Blueprint $table) {
            $table->string('original_name')->after('path')->nullable(); // Add column after 'path'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviewer_files', function (Blueprint $table) {
            $table->dropColumn('original_name'); // Remove column if rolled back
        });
    }
};
