<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nozzle_display_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('station_id')->index();
            $table->unsignedBigInteger('nozzle_id')->index();
            $table->unsignedInteger('sort_order');
            $table->timestamps();
            $table->unique(['station_id', 'nozzle_id']);
            $table->unique(['station_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nozzle_display_orders');
    }
};
