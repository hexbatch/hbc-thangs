<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make this so can run independent of the core
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION thang_update_modified_column()
                RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = now();
                RETURN NEW;
            END;
            $$ language 'plpgsql';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            DROP FUNCTION IF EXISTS thang_update_modified_column();
        ");
    }
};
