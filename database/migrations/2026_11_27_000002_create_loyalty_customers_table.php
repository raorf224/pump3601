<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_customers', function (Blueprint $table) {
            $table->id();
            $table->string('player_id', 191)->unique();
            $table->string('name', 150);
            $table->string('phone', 30)->unique();
            $table->string('email', 191)->unique();
            $table->string('password');
            $table->string('device_id', 191);
            $table->enum('type', ['new', 'referral']);
            $table->string('referral_number', 100)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_customers');
    }
};
