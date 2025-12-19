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
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AuthServiceVerifyRegistrationTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function test_the_verify_registration_throws_when_email_already_verified(): void
    {
        // Arrange
        $user = FakeUserVerifyRegistration::make([
            'id' => 5,
            'email_verified_at' => Carbon::parse('2024-01-01 00:00:00'),
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')->never();

        DB::shouldReceive('transaction')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyRegistration($service);

        // Assert
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عزيزي المستخدم لقد تم تأكيد بريدك الالكتروني مسبقا', $thrown->getMessage());
        $this->assertSame(422, $thrown->getCode());
    }

    public function test_the_verify_registration_throws_when_latest_otp_missing(): void
    {
        // Arrange
        $user = FakeUserVerifyRegistration::make([
            'id' => 6,
            'email_verified_at' => null,
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('getLatestOtp')
            ->once()
            ->with(6, OtpCodePurpose::Verification->value)
            ->andReturn(null);

        DB::shouldReceive('transaction')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyRegistration($service);

        // Assert
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());
        $this->assertSame(422, $thrown->getCode());
    }

    public function test_the_verify_registration_throws_when_otp_code_mismatch(): void
    {
        // Arrange
        $user = FakeUserVerifyRegistration::make([
            'id' => 7,
            'email_verified_at' => null,
        ]);

        $otp = FakeOtpVerifyRegistration::make([
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
            ->with(7, OtpCodePurpose::Verification->value)
            ->andReturn($otp);

        DB::shouldReceive('transaction')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyRegistration($service, '333444');

        // Assert
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());
        $this->assertSame(422, $thrown->getCode());
    }

    public function test_the_verify_registration_throws_when_otp_used(): void
    {
        // Arrange
        $user = FakeUserVerifyRegistration::make([
            'id' => 8,
            'email_verified_at' => null,
        ]);

        $otp = FakeOtpVerifyRegistration::make([
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
            ->with(8, OtpCodePurpose::Verification->value)
            ->andReturn($otp);

        DB::shouldReceive('transaction')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyRegistration($service, '555666');

        // Assert
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());
        $this->assertSame(422, $thrown->getCode());
    }

    public function test_the_verify_registration_throws_when_otp_expired(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2024-04-01 00:00:00'));

        $user = FakeUserVerifyRegistration::make([
            'id' => 9,
            'email_verified_at' => null,
        ]);

        $otp = FakeOtpVerifyRegistration::make([
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
            ->with(9, OtpCodePurpose::Verification->value)
            ->andReturn($otp);

        DB::shouldReceive('transaction')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $thrown = $this->callVerifyRegistration($service, '999000');

        // Assert
        $this->assertInstanceOf(ApiException::class, $thrown);
        $this->assertSame('عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح', $thrown->getMessage());
        $this->assertSame(422, $thrown->getCode());
    }

    public function test_the_verify_registration_updates_user_and_marks_otp_used_in_transaction_on_success(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2024-05-01 12:00:00'));

        $user = FakeUserVerifyRegistration::make([
            'id' => 10,
            'email_verified_at' => null,
            'is_active' => false,
        ]);

        $otp = FakeOtpVerifyRegistration::make([
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
            ->with(10, OtpCodePurpose::Verification->value)
            ->andReturn($otp);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $service = $this->makeAuthService($userRepository, $otpCodesRepository);

        // Act
        $result = $service->verifyRegistration([
            'email' => 'user@example.com',
            'otp_code' => '123456',
        ]);

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertSame($otp, $result['otp']);
        $this->assertArrayHasKey('email_verified_at', $user->updatedAttributes);
        $this->assertNotNull($user->updatedAttributes['email_verified_at']);
        $this->assertSame(true, $user->updatedAttributes['is_active']);
        $this->assertSame(['is_used' => true], $otp->updatedAttributes);
    }

    private function callVerifyRegistration(AuthService $service, string $otpCode = '123456'): ?ApiException
    {
        try {
            $service->verifyRegistration([
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
        OtpCodesRepository $otpCodesRepository,
    ): AuthService {
        return new AuthService(
            $userRepository,
            $otpCodesRepository,
            Mockery::mock(FailedLoginRepository::class),
        );
    }
}

class FakeUserVerifyRegistration extends User
{
    public ?array $updatedAttributes = null;

    public static function make(array $attributes = []): self
    {
        $user = new self();

        foreach ($attributes as $key => $value) {
            $user->attributes[$key] = $value;
        }

        return $user;
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

class FakeOtpVerifyRegistration extends OtpCodes
{
    public ?array $updatedAttributes = null;

    public static function make(array $attributes = []): self
    {
        $otp = new self();

        foreach ($attributes as $key => $value) {
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
