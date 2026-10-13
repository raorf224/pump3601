<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StationAccess
{
    public static function ids(User $user): array
    {
        return self::query($user)->pluck('stations.id')->all();
    }

    public static function query(User $user): Builder
    {
        $query = DB::table('stations');

        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'owner') {
            return $query->where('stations.user_id', $user->id);
        }

        if ($user->role === 'rm') {
            return $query->where('stations.rm_user_id', $user->id);
        }

        if ($user->role === 'tm') {
            return $query->where('stations.tm_user_id', $user->id);
        }

        if ($user->role === 'employee') {
            return $query->whereIn('stations.id', function ($employeeStations) use ($user) {
                $employeeStations->select('station_id')
                    ->from('employees')
                    ->where('user_id', $user->id)
                    ->where('status', 'active');
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
