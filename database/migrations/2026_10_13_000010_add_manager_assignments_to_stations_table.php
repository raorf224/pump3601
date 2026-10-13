<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $roleColumn = DB::selectOne(
            'SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [DB::getDatabaseName(), 'users', 'role']
        );

        if ($roleColumn && strpos($roleColumn->COLUMN_TYPE, "'rm'") === false) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('owner', 'admin', 'employee', 'rm', 'tm') NOT NULL");
        }

        if (!Schema::hasColumn('stations', 'rm_user_id')) {
            Schema::table('stations', function (Blueprint $table) {
                $table->integer('rm_user_id')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('stations', 'tm_user_id')) {
            Schema::table('stations', function (Blueprint $table) {
                $table->integer('tm_user_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (DB::table('users')->whereIn('role', ['rm', 'tm'])->exists()) {
            throw new RuntimeException('Cannot remove RM/TM roles while users still have those roles.');
        }

        $columns = array_filter(['rm_user_id', 'tm_user_id'], function ($column) {
            return Schema::hasColumn('stations', $column);
        });

        if ($columns) {
            Schema::table('stations', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }

        $roleColumn = DB::selectOne(
            'SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [DB::getDatabaseName(), 'users', 'role']
        );

        if ($roleColumn && strpos($roleColumn->COLUMN_TYPE, "'rm'") !== false) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('owner', 'admin', 'employee') NOT NULL");
        }
    }
};
