<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sourcePermissionId = DB::table('permissions')
            ->where('name', 'loyalty_coupons')
            ->where('guard_name', 'web')
            ->value('id');

        foreach (['loyalty_customers', 'loyalty_coupon_assignments'] as $permissionName) {
            $permissionId = DB::table('permissions')
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->value('id');

            if (!$permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!$sourcePermissionId) {
                continue;
            }

            $rolePermissions = DB::table('role_has_permissions')
                ->where('permission_id', $sourcePermissionId)
                ->get(['role_id', 'create', 'read', 'update', 'delete']);

            foreach ($rolePermissions as $rolePermission) {
                $alreadyAssigned = DB::table('role_has_permissions')
                    ->where('role_id', $rolePermission->role_id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$alreadyAssigned) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $rolePermission->role_id,
                        'permission_id' => $permissionId,
                        'create' => $rolePermission->create,
                        'read' => $rolePermission->read,
                        'update' => $rolePermission->update,
                        'delete' => $rolePermission->delete,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['loyalty_customers', 'loyalty_coupon_assignments'] as $permissionName) {
            $permissionId = DB::table('permissions')
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->value('id');

            if ($permissionId) {
                DB::table('role_has_permissions')
                    ->where('permission_id', $permissionId)
                    ->delete();
                DB::table('permissions')->where('id', $permissionId)->delete();
            }
        }
    }
};
