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

class AuthServiceResendOtpTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function test_the_resend_otp_throws_when_purpose_is_verification_and_email_already_verified(): void
    {
        // Arrange
        $user = FakeUserResendOtp::make([
            'id' => 5,
            'email_verified_at' => Carbon::parse('2024-01-01 00:00:00'),
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        $thrownException = null;

        // Act
        try {
            $service->resendOtp('user@example.com', OtpCodePurpose::Verification->value);
        } catch (ApiException $exception) {
            $thrownException = $exception;
        }

        // Assert
        $this->assertNotNull($thrownException, 'Expected ApiException was not thrown.');
        $this->assertSame('عزيزي المستخدم لقد تم تأكيد بريدك الالكتروني مسبقا', $thrownException->getMessage());
        $this->assertSame(422, $thrownException->getCode());
    }

    public function test_the_resend_otp_creates_new_otp_for_verification_when_email_not_verified(): void
    {
        // Arrange
        $user = FakeUserResendOtp::make([
            'id' => 10,
            'email_verified_at' => null,
        ]);

        $otp = FakeOtpResendOtp::make([
            'id' => 100,
            'otp_code' => '654321',
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')
            ->once()
            ->with(10, OtpCodePurpose::Verification->value)
            ->andReturn($otp);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $result = $service->resendOtp('user@example.com', OtpCodePurpose::Verification->value);

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertSame($otp, $result['otp']);
    }

    public function test_the_resend_otp_creates_new_otp_for_given_purpose_reset(): void
    {
        // Arrange
        $user = FakeUserResendOtp::make([
            'id' => 20,
            'email_verified_at' => Carbon::parse('2024-03-01 12:00:00'),
        ]);

        $otp = FakeOtpResendOtp::make([
            'id' => 200,
            'otp_code' => '123987',
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')
            ->once()
            ->with(20, OtpCodePurpose::Reset->value)
            ->andReturn($otp);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $result = $service->resendOtp('user@example.com', OtpCodePurpose::Reset->value);

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertSame($otp, $result['otp']);
    }

    private function makeAuthService(
        UserRepository $userRepository,
        OtpCodesRepository $otpCodesRepository,
    ): AuthService {
        return new AuthService(
            $userRepository,
            $otpCodesRepository,
            Mockery::mock(FailedLoginRepository::class),
        );
    }
}

class FakeUserResendOtp extends User
{
    public static function make(array $attributes = []): self
    {
        $user = new self();

        foreach ($attributes as $key => $value) {
            $user->attributes[$key] = $value;
        }

        return $user;
    }
}

class FakeOtpResendOtp extends OtpCodes
{
    public static function make(array $attributes = []): self
    {
        $otp = new self();

        foreach ($attributes as $key => $value) {
            $otp->attributes[$key] = $value;
        }

        return $otp;
    }
}
