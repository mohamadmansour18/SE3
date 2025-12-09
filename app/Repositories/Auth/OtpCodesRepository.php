<?php

namespace App\Repositories\Auth;

use App\Models\OtpCodes;
use Illuminate\Database\Eloquent\Model;

class OtpCodesRepository
{
    public function createOtpFor(int $userId, string $purpose): Model
    {
        return OtpCodes::create([
            'user_id' => $userId ,
            'otp_code' => random_int(100000 , 999999),
            'expires_at' => now()->addMinutes(5),
            'is_used' => false ,
            'purpose' => $purpose
        ]);
    }

    public function getLatestOtp(int $userId , string $purpose): ?OtpCodes
    {
        return OtpCodes::where('user_id', $userId)
            ->where('purpose' , $purpose)
            ->latest('id')
            ->first();
    }
}
