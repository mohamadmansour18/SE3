<?php

namespace App\Repositories\Auth;

use App\Models\FailedLogin;
use App\Models\User;

class FailedLoginRepository
{
    public function countRecentFailedLogins(User $user , int $minutes): int
    {
        return FailedLogin::query()
            ->where('user_id' , $user->id)
            ->where('occurred_at' , '>=' , now()->subMinutes($minutes))
            ->count();
    }

    public function recordFailedLogin(User $user, ?string $ip, ?string $userAgent): void
    {
        FailedLogin::create([
            'user_id'     => $user->id,
            'ip_address'  => $ip,
            'user_agent'  => $userAgent,
            'occurred_at' => now(),
        ]);
    }

    public function clearFailedLogins(User $user): void
    {
        FailedLogin::where('user_id', $user->id)->delete();
    }
}
