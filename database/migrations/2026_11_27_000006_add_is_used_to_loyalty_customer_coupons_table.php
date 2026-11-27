<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('loyalty_customer_coupons', 'is_used')) {
            Schema::table('loyalty_customer_coupons', function (Blueprint $table) {
                $table->string('is_used', 45)->default('0')->after('coupon_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loyalty_customer_coupons', 'is_used')) {
            Schema::table('loyalty_customer_coupons', function (Blueprint $table) {
                $table->dropColumn('is_used');
            });
        }
    }
};
