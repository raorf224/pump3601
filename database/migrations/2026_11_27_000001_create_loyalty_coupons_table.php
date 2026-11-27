<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('coupon_for', ['welcome', 'all', 'specific', 'referall'])->default('all');
            $table->enum('coupon_type', ['percent', 'flat']);
            $table->decimal('value', 10, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('valid_from');
            $table->date('valid_to');
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamps();
            $table->index(['status', 'valid_from', 'valid_to'], 'coupons_status_validity_idx');
        });

        $permissionExists = DB::table('permissions')
            ->where('name', 'loyalty_coupons')
            ->where('guard_name', 'web')
            ->exists();

        if (!$permissionExists) {
            DB::table('permissions')->insert([
                'name' => 'loyalty_coupons',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'loyalty_coupons')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        Schema::dropIfExists('coupons');
    }
};
