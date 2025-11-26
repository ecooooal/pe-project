<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviewers', function (Blueprint $table) {
            $table->dropUnique('reviewers_email_unique'); // Drop the unique constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    DB::statement("
        DO \$\$
        BEGIN
            IF NOT EXISTS (
                SELECT 1 FROM pg_constraint WHERE conname = 'reviewers_email_unique'
            ) THEN
                ALTER TABLE reviewers ADD CONSTRAINT reviewers_email_unique UNIQUE (email);
            END IF;
        END
        \$\$ LANGUAGE plpgsql;
    ");
}
};