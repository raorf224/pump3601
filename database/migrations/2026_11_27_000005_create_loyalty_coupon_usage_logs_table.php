<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_coupon_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->restrictOnDelete();
            $table->integer('station_id');
            $table->foreign('station_id')
                ->references('id')
                ->on('stations')
                ->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('loyalty_customers')->restrictOnDelete();
            $table->timestamp('used_at');
            $table->timestamps();
            $table->index(['customer_id', 'coupon_id'], 'loyalty_usage_customer_coupon_idx');
            $table->index(['station_id', 'used_at'], 'loyalty_usage_station_used_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_coupon_usage_logs');
    }
};
