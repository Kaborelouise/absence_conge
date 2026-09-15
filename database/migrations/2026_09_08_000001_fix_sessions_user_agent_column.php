<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'sessions'");
        $names = array_map(static fn ($column) => $column->column_name, $columns);

        if (in_array('user_Agent', $names, true) && ! in_array('user_agent', $names, true)) {
            DB::statement('ALTER TABLE "sessions" RENAME COLUMN "user_Agent" TO "user_agent";');
        }

        $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'sessions'");
        $names = array_map(static fn ($column) => $column->column_name, $columns);

        if (! in_array('user_agent', $names, true)) {
            DB::statement('ALTER TABLE "sessions" ADD COLUMN "user_agent" TEXT NULL;');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'sessions'");
        $names = array_map(static fn ($column) => $column->column_name, $columns);

        if (in_array('user_agent', $names, true) && ! in_array('user_Agent', $names, true)) {
            DB::statement('ALTER TABLE "sessions" RENAME COLUMN "user_agent" TO "user_Agent";');
        }
    }
};
