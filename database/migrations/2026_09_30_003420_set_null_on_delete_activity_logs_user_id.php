<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_user_id_fkey');
        DB::statement('ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_user_id_foreign');
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN user_id DROP NOT NULL');
        DB::statement('ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void {}
};