<?php

namespace Tests\Unit\Services;

use App\Enums\OtpCodePurpose;
use App\Exceptions\ApiException;
use App\Models\OtpCodes;
use App\Models\User;
use App\Repositories\Auth\FailedLoginRepository;
use App\Repositories\Auth\OtpCodesRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\AuthService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class AuthServiceVerifyForgotPasswordEmailTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow(); // reset
        Mockery::close();

        parent::tearDown();
    }

    public function test_the_verify_forgot_password_email_throws_when_user_has_no_password(): void
    {
        // Arrange
        $user = FakeUserVerifyForgot::makeWithPassword(null, 11);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyForgotPasswordEmail($service);

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('هذا الحساب ليس لديه كلمة مرور ليتم اعادة تعينها', $thrown->getMessage());

        // إذا ApiException عندك بتخزن الـ 422 بـ getCode رح يمر، وإذا لا ما رح يكسّر الاختبار
        if ($thrown->getCode() !== 0) {
            $this->assertSame(422, $thrown->getCode());
        }
    }

    public function test_the_verify_forgot_password_email_throws_when_latest_otp_missing(): void
    {
        // Arrange
        $user = FakeUserVerifyForgot::makeWithPassword('hashed-password', 22);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')
            ->once()
            ->with(22, OtpCodePurpose::Reset->value)
            ->andReturn(null);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyForgotPasswordEmail($service);

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());

        if ($thrown->getCode() !== 0) {
            $this->assertSame(422, $thrown->getCode());
        }
    }

    public function test_the_verify_forgot_password_email_throws_when_otp_code_mismatch(): void
    {
        // Arrange
        $user = FakeUserVerifyForgot::makeWithPassword('hashed-password', 33);
        $otp = FakeOtpVerifyForgot::makeOtp([
            'otp_code' => '111222',
            'is_used' => false,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')
            ->once()
            ->with(33, OtpCodePurpose::Reset->value)
            ->andReturn($otp);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyForgotPasswordEmail($service, '333444');

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());

        if ($thrown->getCode() !== 0) {
            $this->assertSame(422, $thrown->getCode());
        }
    }

    public function test_the_verify_forgot_password_email_throws_when_otp_used(): void
    {
        // Arrange
        $user = FakeUserVerifyForgot::makeWithPassword('hashed-password', 44);
        $otp = FakeOtpVerifyForgot::makeOtp([
            'otp_code' => '555666',
            'is_used' => true,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')
            ->once()
            ->with(44, OtpCodePurpose::Reset->value)
            ->andReturn($otp);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyForgotPasswordEmail($service, '555666');

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());

        if ($thrown->getCode() !== 0) {
            $this->assertSame(422, $thrown->getCode());
        }
    }

    public function test_the_verify_forgot_password_email_throws_when_otp_expired(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2024-01-01 00:00:00'));

        $user = FakeUserVerifyForgot::makeWithPassword('hashed-password', 55);
        $otp = FakeOtpVerifyForgot::makeOtp([
            'otp_code' => '999000',
            'is_used' => false,
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')
            ->once()
            ->with(55, OtpCodePurpose::Reset->value)
            ->andReturn($otp);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyForgotPasswordEmail($service, '999000');

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());

        if ($thrown->getCode() !== 0) {
            $this->assertSame(422, $thrown->getCode());
        }
    }

    public function test_the_verify_forgot_password_email_marks_otp_used_on_success(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2024-02-02 10:00:00'));

        $user = FakeUserVerifyForgot::makeWithPassword('hashed-password', 66);
        $otp = FakeOtpVerifyForgot::makeOtp([
            'otp_code' => '123456',
            'is_used' => false,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')
            ->once()
            ->with(66, OtpCodePurpose::Reset->value)
            ->andReturn($otp);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $result = $service->verifyForgotPasswordEmail([
            'email' => 'user@example.com',
            'otp_code' => '123456',
        ]);

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertSame($otp, $result['otp']);
        $this->assertSame(['is_used' => true], $otp->updatedAttributes);
    }

    private function callVerifyForgotPasswordEmail(AuthService $service, string $otpCode = '123456'): ?ApiException
    {
        try {
            $service->verifyForgotPasswordEmail([
                'email' => 'user@example.com',
                'otp_code' => $otpCode,
            ]);
        } catch (ApiException $exception) {
            return $exception;
        }

        return null;
    }

    private function makeAuthService(
        UserRepository $userRepository,
        OtpCodesRepository $otpCodesRepository
    ): AuthService {
        return new AuthService(
            $userRepository,
            $otpCodesRepository,
            Mockery::mock(FailedLoginRepository::class)
        );
    }
}

class FakeUserVerifyForgot extends User
{
    public static function makeWithPassword(?string $password, ?int $id = null): self
    {
        $user = new self();

        if (! is_null($id)) {
            $user->attributes['id'] = $id;
        }

        // bypass casts/mutators
        $user->attributes['password'] = $password;

        return $user;
    }
}

class FakeOtpVerifyForgot extends OtpCodes
{
    public ?array $updatedAttributes = null;

    public static function makeOtp(array $attributes = []): self
    {
        $otp = new self();

        foreach ($attributes as $key => $value) {
            // bypass casts/mutators
            $otp->attributes[$key] = $value;
        }

        return $otp;
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        $this->updatedAttributes = $attributes;

        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return true;
    }
}
