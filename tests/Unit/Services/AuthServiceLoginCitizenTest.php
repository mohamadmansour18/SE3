<?php

namespace Tests\Unit\Services;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\Auth\FailedLoginRepository;
use App\Repositories\Auth\OtpCodesRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthServiceLoginCitizenTest extends TestCase
{
    protected function tearDown(): void
{
    Mockery::close();
    parent::tearDown();
}


    public function test_the_login_citizen_throws_when_user_is_inactive(): void
    {
        // Arrange
        $user = FakeUserLoginCitizen::make([
            'is_active' => false,
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);
        $userRepository->shouldReceive('updateLastLoginAt')->never();

        $failedLoginRepository = Mockery::mock(FailedLoginRepository::class);
        $failedLoginRepository->shouldReceive('recordFailedLogin')->never();
        $failedLoginRepository->shouldReceive('countRecentFailedLogins')->never();
        $failedLoginRepository->shouldReceive('clearFailedLogins')->never();

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')->never();
        $otpCodesRepository->shouldReceive('getLatestOtp')->never();

        Hash::shouldReceive('check')->never();
        DB::shouldReceive('transaction')->never();
        JWTAuth::shouldReceive('fromUser')->never();
        JWTAuth::shouldReceive('factory')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository, $failedLoginRepository);
        $thrown = null;

        // Act
        try {
            $service->loginCitizen([
                'email' => 'user@example.com',
                'password' => 'plain-password',
            ]);
        } catch (ApiException $exception) {
            $thrown = $exception;
        }

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertSame('تم قفل حسابك لاسباب تتعلق بسياسة الاستخدام', $thrown->getMessage());
    }

    public function test_the_login_citizen_throws_when_password_is_invalid(): void
    {
        // Arrange
        $user = FakeUserLoginCitizen::make([
            'is_active' => true,
            'password' => 'hashed-password',
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);
        $userRepository->shouldReceive('updateLastLoginAt')->never();

        $failedLoginRepository = Mockery::mock(FailedLoginRepository::class);
        $failedLoginRepository->shouldReceive('recordFailedLogin')
            ->once()
            ->with($user, '127.0.0.1', 'unit-test');
        $failedLoginRepository->shouldReceive('countRecentFailedLogins')
            ->once()
            ->with($user, 5)
            ->andReturn(0);
        $failedLoginRepository->shouldReceive('clearFailedLogins')->never();

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')->never();
        $otpCodesRepository->shouldReceive('getLatestOtp')->never();

        Hash::shouldReceive('check')
            ->once()
            ->with('plain-password', Mockery::type('string'))
            ->andReturn(false);
        DB::shouldReceive('transaction')->never();
        JWTAuth::shouldReceive('fromUser')->never();
        JWTAuth::shouldReceive('factory')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository, $failedLoginRepository);
        $thrown = null;

        // Act
        try {
            $service->loginCitizen([
                'email' => 'user@example.com',
                'password' => 'plain-password',
                'ip' => '127.0.0.1',
                'user_agent' => 'unit-test',
            ]);
        } catch (ApiException $exception) {
            $thrown = $exception;
        }

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertSame('بيانات الدخول غير صحيحة', $thrown->getMessage());
    }

    public function test_the_login_citizen_throws_when_email_not_verified(): void
    {
        // Arrange
        $user = FakeUserLoginCitizen::make([
            'is_active' => true,
            'password' => 'hashed-password',
            'email_verified_at' => null,
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);
        $userRepository->shouldReceive('updateLastLoginAt')->never();

        $failedLoginRepository = Mockery::mock(FailedLoginRepository::class);
        $failedLoginRepository->shouldReceive('recordFailedLogin')->never();
        $failedLoginRepository->shouldReceive('countRecentFailedLogins')->never();
        $failedLoginRepository->shouldReceive('clearFailedLogins')->never();

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')->never();
        $otpCodesRepository->shouldReceive('getLatestOtp')->never();

        Hash::shouldReceive('check')
            ->once()
            ->with('plain-password', Mockery::type('string'))
            ->andReturn(true);
        DB::shouldReceive('transaction')->never();
        JWTAuth::shouldReceive('fromUser')->never();
        JWTAuth::shouldReceive('factory')->never();

        $service = $this->makeAuthService($userRepository, $otpCodesRepository, $failedLoginRepository);
        $thrown = null;

        // Act
        try {
            $service->loginCitizen([
                'email' => 'user@example.com',
                'password' => 'plain-password',
            ]);
        } catch (ApiException $exception) {
            $thrown = $exception;
        }

        // Assert
        $this->assertNotNull($thrown, 'Expected ApiException was not thrown.');
        $this->assertSame('يجب ان تقوم بتأكيد الحساب قبل القيام بعملية تسجيل الدخول', $thrown->getMessage());
    }

    public function test_the_login_citizen_returns_token_and_user_on_success(): void
    {
        // Arrange
        $user = FakeUserLoginCitizen::make([
            'is_active' => true,
            'password' => 'hashed-password',
            'email_verified_at' => Carbon::parse('2024-01-01 00:00:00'),
        ]);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);
        $userRepository->shouldReceive('updateLastLoginAt')
            ->once()
            ->with($user);

        $failedLoginRepository = Mockery::mock(FailedLoginRepository::class);
        $failedLoginRepository->shouldReceive('recordFailedLogin')->never();
        $failedLoginRepository->shouldReceive('countRecentFailedLogins')->never();
        $failedLoginRepository->shouldReceive('clearFailedLogins')
            ->once()
            ->with($user);

        $otpCodesRepository = Mockery::mock(OtpCodesRepository::class);
        $otpCodesRepository->shouldReceive('createOtpFor')->never();
        $otpCodesRepository->shouldReceive('getLatestOtp')->never();

        Hash::shouldReceive('check')
            ->once()
            ->with('plain-password', Mockery::type('string'))
            ->andReturn(true);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $factory = Mockery::mock();
        $factory->shouldReceive('getTTL')
            ->once()
            ->andReturn(60);

        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->with($user)
            ->andReturn('fake-token');
        JWTAuth::shouldReceive('factory')
            ->once()
            ->andReturn($factory);

        $service = $this->makeAuthService($userRepository, $otpCodesRepository, $failedLoginRepository);

        // Act
        $result = $service->loginCitizen([
            'email' => 'user@example.com',
            'password' => 'plain-password',
        ]);

        // Assert
        $this->assertSame('fake-token', $result['token']);
        $this->assertSame(3600, $result['expires_in']);
        $this->assertSame($user, $result['user']);
    }

    private function makeAuthService(
        UserRepository $userRepository,
        OtpCodesRepository $otpCodesRepository,
        FailedLoginRepository $failedLoginRepository,
    ): AuthService {
        return new AuthService(
            $userRepository,
            $otpCodesRepository,
            $failedLoginRepository,
        );
    }
}

class FakeUserLoginCitizen extends User
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
