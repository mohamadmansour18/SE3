<?php

namespace App\Services\Proxy;

use App\Exceptions\ApiException;
use App\Helpers\TextHelper;
use App\Jobs\SendOtpCode;

use App\Models\User;
use App\Services\Contracts\AuthServiceInterface;
use App\Traits\AspectTrait;
use Illuminate\Support\Facades\Auth;

class AuthServiceAspect implements AuthServiceInterface
{
    use AspectTrait;

    public function __construct(
        protected AuthServiceInterface $inner
    )
    {}

    public function registerCitizen(array $data): array
    {
        return $this->around(
            before: function () {
                if(Auth::check())
                {
                    throw new ApiException('حسابك اساسا موجود في النظام لايمكنك انشاء الحساب مرة ثانية' , 422);
                }
            },
            callback: fn() => $this->inner->registerCitizen($data),

            after: function(array $result){
                SendOtpCode::dispatch(email:  $result['user']->email , name:  $result['user']->name , code:  $result['otp']->otp_code , purpose: $result['otp']->purpose->value);
            },
            audit: function (array $result){
                return [
                    'actor_id' => $result['user']->id,
                    'subject_type' => User::class,
                    'subject_id'   => $result['user']->id,
                    'changes' => [
                        'action' => 'register_citizen',
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                        'national_number' => $result['user']->national_number,
                    ]
                ];
            },
        );
    }

    public function loginCitizen(array $data): array
    {
        $result = $this->around(
            callback: fn() => $this->inner->loginCitizen($data),
            audit: function(array $result){
                return [
                    'actor_id' => $result['user']->id,
                    'subject_type' => User::class,
                    'subject_id'   => $result['user']->id,
                    'changes' => [
                        'action' => 'login',
                        'ip' => $data['ip'] ?? null,
                    ]
                ];
            },
        );

        return [
            'token' => $result['token'],
            'expires_in' => $result['expires_in'],
            'name' => TextHelper::fixBidi("مرحبا صديقي المواطن {$result['user']->name}")
        ];
    }

    public function verifyRegistration(array $data): array
    {
        return $this->around(
            callback: fn() => $this->inner->verifyRegistration($data),
            audit: function(array $result){
                return [
                    'actor_id' => $result['user']->id,
                    'subject_type' => User::class,
                    'subject_id'   => $result['user']->id,
                    'changes' => [
                        'action' => 'verify_registration',
                        'email' => $data['email'] ?? null,
                        'otp' => $result['otp'] ?? null,
                    ]
                ];
            },
        );
    }

    public function resendOtp(string $email , string $purpose): array
    {
        return $this->around(
            callback: fn() => $this->inner->resendOtp($email , $purpose),
            after: function(array $result){
                SendOtpCode::dispatch(email: $result['user']->email , name:  $result['user']->name , code:  $result['otp']->otp_code , purpose: $result['otp']->purpose->value);
            },
            audit: function(array $result){
                return [
                    'actor_id' => $result['user']->id,
                    'subject_type' => User::class,
                    'subject_id'   => $result['user']->id,
                    'changes' => [
                        'action' => 'resend_Otp',
                        'purpose' => $result['otp']->purpose->value,
                        'email' => $email ?? null,
                        'otp' => $result['otp'] ?? null,
                    ]
                ];
            },
        );
    }

    ////////////////////////////////////////////////////////////

    public function forgotPassword(string $email): array
    {
         return $this->around(
             callback: fn() => $this->inner->forgotPassword($email),
             after: function(array $result){
                 SendOtpCode::dispatch(email: $result['user']->email , name:  $result['user']->name , code:  $result['otp']->otp_code , purpose: $result['otp']->purpose->value);
             },
             audit: function (array $result){
                 return [
                     'actor_id' => $result['user']->id,
                     'subject_type' => User::class,
                     'subject_id'   => $result['user']->id,
                     'changes' => [
                         'action' => 'forgot_password',
                         'email' => $email ?? null,
                         'otp' => $result['otp'] ?? null,
                     ]
                 ];
             },
         );
    }

    public function verifyForgotPasswordEmail(array $data): array
    {
        return $this->around(
            callback: fn() => $this->inner->verifyForgotPasswordEmail($data),
            audit: function(array $result){
                return [
                    'actor_id' => $result['user']->id,
                    'subject_type' => User::class,
                    'subject_id'   => $result['user']->id,
                    'changes' => [
                        'action' => 'verify_forgot_password_email',
                        'email' => $data['email'] ?? null,
                        'otp' => $result['otp'] ?? null,
                    ]
                ];
            },
        );
    }

    public function resetPassword(array $data):array
    {
        return $this->around(
            callback: fn() => $this->inner->resetPassword($data),
            audit: function(array $result){
                return [
                    'actor_id' => $result['user']->id,
                    'subject_type' => User::class,
                    'subject_id'   => $result['user']->id,
                    'changes' => [
                        'action' => 'reset_password',
                        'email' => $data['email'] ?? null,
                    ]
                ];
            },
        );
    }

}
