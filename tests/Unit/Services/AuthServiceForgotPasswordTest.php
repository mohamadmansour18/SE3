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
use Mockery;
use Tests\TestCase;


class AuthServiceForgotPasswordTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_the_forgot_password_throws_when_user_has_no_password(): void
    {
        // Arrange
        $user = FakeUserForgot::makeWithPassword(null, 15);
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);
        $caughtException = null;

        // Act
        try {
            $service->forgotPassword('user@example.com');
        } catch (ApiException $exception) {
            $caughtException = $exception;
        }

        // Assert
        if (! $caughtException) {
            $this->fail('Expected ApiException was not thrown.');
        }

        $this->assertSame('هذا الحساب ليس لديه كلمة مرور ليتم اعادة تعينها', $caughtException->getMessage());
    }

    public function test_the_forgot_password_creates_reset_otp_when_user_has_password(): void
    {
        // Arrange
        $user = FakeUserForgot::makeWithPassword('hashed-password', 42);
        $otp = FakeOtp::makeOtp(['otp_code' => '654321']);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')
            ->once()
            ->with(42, OtpCodePurpose::Reset->value)
            ->andReturn($otp);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $result = $service->forgotPassword('user@example.com');

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertSame($otp, $result['otp']);
    }

    private function makeAuthService(
        UserRepository $userRepository,
        OtpCodesRepository $otpCodesRepository
    ): AuthService {
        return new AuthService(
            $userRepository,
            $otpCodesRepository,
            Mockery::mock(FailedLoginRepository::class),
        );
    }
}

class FakeUserForgot extends User
{
    public static function makeWithPassword(?string $password, ?int $id = null): self
    {
        $user = new self();

        if (! is_null($id)) {
            $user->attributes['id'] = $id;
        }

        $user->attributes['password'] = $password;

        return $user;
    }
}

class FakeOtp extends OtpCodes
{
    public static function makeOtp(array $attributes = []): self
    {
        $otp = new self();

        foreach ($attributes as $key => $value) {
            $otp->attributes[$key] = $value;
        }

        return $otp;
    }
}
