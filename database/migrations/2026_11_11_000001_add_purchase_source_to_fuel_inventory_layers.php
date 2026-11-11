<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_inventory_layers', function (Blueprint $table) {
            $table->integer('oil_purchase_id')->nullable()->index();
            $table->integer('oil_received_tank_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('fuel_inventory_layers', function (Blueprint $table) {
            $table->dropIndex(['oil_purchase_id']);
            $table->dropIndex(['oil_received_tank_id']);
            $table->dropColumn(['oil_purchase_id', 'oil_received_tank_id']);
        });
    }
};
