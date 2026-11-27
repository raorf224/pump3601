<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_customer_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('loyalty_customers')
                ->cascadeOnDelete();
            $table->foreignId('coupon_id')
                ->constrained('coupons')
                ->cascadeOnDelete();
            $table->string('is_used', 45)->default('0');
            $table->timestamp('assigned_at');
            $table->timestamps();
            $table->unique(['customer_id', 'coupon_id'], 'loyalty_customer_coupon_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_customer_coupons');
    }
};
