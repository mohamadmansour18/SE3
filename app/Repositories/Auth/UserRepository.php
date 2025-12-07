<?php

namespace App\Repositories\Auth;

use App\Enums\UserRole;
use App\Models\FailedLogin;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function createCitizenWithProfile(array $userData , array $profileData)
    {
        return DB::transaction(function() use($userData, $profileData){
            $user = User::query()->create($userData);
            $user->citizenProfile()->create($profileData);
            return $user->fresh('citizenProfile');
        });
    }

    public function findByEmail(string $email): User
    {
        return User::where('email' , $email)->first();
    }

    public function updateLastLoginAt(User $user): void
    {
        $user->last_login_at = now();
        $user->save();
    }

}
