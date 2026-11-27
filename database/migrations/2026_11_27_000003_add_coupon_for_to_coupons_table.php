<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('coupons', 'coupon_for')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->enum('coupon_for', ['welcome', 'all', 'specific', 'referall'])
                    ->default('all')
                    ->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'coupon_for')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('coupon_for');
            });
        }
    }
};
