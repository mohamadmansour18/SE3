<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OtpCodePurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginCitizenRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Http\Requests\RegisterCitizenRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\ProfileServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly ProfileServiceInterface $profileService,
    ){}

    public function registerCitizen(RegisterCitizenRequest $request): JsonResponse
    {
        $data = $this->authService->registerCitizen($request->validated());

        return $this->successResponse(
            message: "تم انشاء بريدك بنجاح ، الرجاء القيام بتأكيد بريدك الالكتروني" ,
            statusCode: 201
        );
    }

    public function loginCitizen(LoginCitizenRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['ip'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        $result = $this->authService->loginCitizen($data);

        return $this->dataResponse(
            data: $result ,
        );
    }

    public function verifyRegistrationCitizen(OtpVerificationRequest $request): JsonResponse
    {
        $this->authService->verifyRegistration($request->validated());

        return $this->successResponse("تم تأكيد حسابك على تطبيق تواصل بنجاح");
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $this->authService->resendOtp($request->validated()['email'] , OtpCodePurpose::Verification->value);

        return $this->successResponse('تم إرسال رمز تحقق جديد إلى بريدك' , 200);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->validated()['email']);

        return $this->successResponse("تم إرسال رمز التحقق إلى بريدك الإلكتروني المدخل" , 200);
    }

    public function verifyForgotPasswordEmail(OtpVerificationRequest $request): JsonResponse
    {
        $this->authService->verifyForgotPasswordEmail($request->validated());

        return $this->successResponse("تم تأكيد بريدك الالكتروني المستخدم لاعادة تعين كلمة المرور" , 200);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return $this->successResponse('تم تعيين كلمة المرور الجديدة بنجاح ، يمكنك تسجيل الدخول الآن' , 201);
    }

    public function resendPasswordResetOtp(ResendOtpRequest $request): JsonResponse
    {
        $this->authService->resendOtp($request->validated()['email'] , OtpCodePurpose::Reset->value);

        return $this->successResponse('تم إرسال رمز تحقق جديد إلى بريدك' , 200);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////

    public function logout(): JsonResponse
    {

        auth('api')->logout();

        return $this->successResponse("تم تسجيل الخروج من حسابك بنجاح ، شكرا لاستخدامك تطبيق تواصل" , 200);
    }

    public function refresh(): JsonResponse
    {
        $newToken = JWTAuth::parseToken()->refresh();

        return $this->dataResponse([
            'newToken' => $newToken,
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ],200);
    }

}
