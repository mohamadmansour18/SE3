<?php

namespace App\Services;

use App\Enums\OtpCodePurpose;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Jobs\FailedLogin;
use App\Models\FcmToken;
use App\Repositories\Auth\FailedLoginRepository;
use App\Repositories\Auth\OtpCodesRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly OtpCodesRepository $otpCodesRepository,
        private readonly FailedLoginRepository $failedLoginRepository,
    ){}

    ///////////////////////////////////////////////////////////////////

    public function registerCitizen(array $data): array
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::CITIZEN->value ,
            'last_login_at' => null ,
            'is_active' => false
        ];

        $profileData = [
            'national_number' => $data['national_number'],
        ];

        $user = $this->userRepository->createCitizenWithProfile($userData, $profileData);

        $otp = $this->otpCodesRepository->createOtpFor($user->id , OtpCodePurpose::Verification->value);

        return [
            'user' => $user,
            'otp' => $otp,
        ];
    }

    public function loginCitizen(array $data): array
    {

        $user = $this->userRepository->findByEmail($data['email']);

        if(!$user->is_active)
        {
            throw new ApiException("تم قفل حسابك لاسباب تتعلق بسياسة الاستخدام ، يرجى مراجعة وزارة الاتصالات للاستفسار عن الحساب" , 422);
        }

        $maxAttempts  = 3;
        $decayMinutes = 5;

        if(!Hash::check($data['password'] , $user->password))
        {
            $this->failedLoginRepository->recordFailedLogin($user , $data['ip'] , $data['user_agent']);

            $recentFails = $this->failedLoginRepository->countRecentFailedLogins($user , $decayMinutes);

            if($recentFails >= $maxAttempts)
            {
                FailedLogin::dispatch($user , [
                    'ip_address'  => $data['ip'],
                    'user_agent'  => $data['user_agent'],
                    'occurred_at' => now()->toDateTimeString(),
                ]);
            }

            throw new ApiException('بيانات الدخول غير صحيحة' , 422);
        }

        if(is_null($user->email_verified_at))
        {
            throw new ApiException('يجب ان تقوم بتأكيد الحساب قبل القيام بعملية تسجيل الدخول' , 422);
        }

        DB::transaction(function () use ($user){
            $this->failedLoginRepository->clearFailedLogins($user);
            $this->userRepository->updateLastLoginAt($user);
        });

        $token = JWTAuth::fromUser($user);

        if(!empty($data['fcm_token']))
        {
            FcmToken::updateOrCreate(
                ['token' =>$data['fcm_token']],
                ['user_id' => $user->id]
            );
        }

        return [
            'token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $user
        ];
    }

    public function verifyRegistration(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if($user->email_verified_at)
        {
            throw new ApiException('عزيزي المستخدم لقد تم تأكيد بريدك الالكتروني مسبقا' , 422);
        }

        $latestOtp = $this->otpCodesRepository->getLatestOtp($user->id , OtpCodePurpose::Verification->value);

        if(!$latestOtp || $latestOtp->otp_code !== $data['otp_code'] || $latestOtp->is_used || $latestOtp->expires_at < now())
        {
            throw new ApiException('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح' , 422);
        }

        DB::transaction(function () use ($user , $latestOtp){
            $user->update([
                'email_verified_at' => now(),
                'is_active' => true
            ]);

            $latestOtp->update([
               'is_used' => true
            ]);
        });

        return [
            'user' => $user,
            'otp' => $latestOtp,
        ];
    }

    public function resendOtp(string $email , string $purpose): array
    {
        $user = $this->userRepository->findByEmail($email);

        if($user->email_verified_at && $purpose === OtpCodePurpose::Verification->value)
        {
            throw new ApiException('عزيزي المستخدم لقد تم تأكيد بريدك الالكتروني مسبقا' , 422);
        }

        $otp = $this->otpCodesRepository->createOtpFor($user->id , $purpose);

        return [
            'user' => $user,
            'otp' => $otp,
        ];
    }

    ///////////////////////////////////////////////////////////////////

    public function forgotPassword(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if(!$user->password)
        {
            throw new ApiException('هذا الحساب ليس لديه كلمة مرور ليتم اعادة تعينها' , 422);
        }

        $otp = $this->otpCodesRepository->createOtpFor($user->id , OtpCodePurpose::Reset->value);

        return [
            'user' => $user,
            'otp' => $otp,
        ];
    }

    public function verifyForgotPasswordEmail(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if(!$user->password)
        {
            throw new ApiException('هذا الحساب ليس لديه كلمة مرور ليتم اعادة تعينها' , 422);
        }

        $latestOtp = $this->otpCodesRepository->getLatestOtp($user->id , OtpCodePurpose::Reset->value);

        if(!$latestOtp || $latestOtp->otp_code !== $data['otp_code'] || $latestOtp->is_used || $latestOtp->expires_at < now())
        {
            throw new ApiException('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح' , 422);
        }

        $latestOtp->update([
            'is_used' => true
        ]);

        return [
            'user' => $user,
            'otp' => $latestOtp,
        ];
    }

    public function resetPassword(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if(!$user->password)
        {
            throw new ApiException('هذا الحساب ليس لديه كلمة مرور ليتم اعادة تعينها' , 422);
        }

        if(Hash::check($data['password'] , $user->password))
        {
            throw new ApiException('يرجى اختيار كلمة مرور مختلفة عن الكلمة الحالية');
        }

        $user->update([
            'password' => Hash::make($data['password']) ,
        ]);

        return [
            'user' => $user,
        ];
    }


}
