<?php

namespace Tests\Unit\Services;

use App\Exceptions\ApiException;
use App\Repositories\Auth\FailedLoginRepository;
use App\Repositories\Auth\OtpCodesRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;
use App\Models\User;


class AuthServiceResetPasswordTest extends TestCase
{
    protected function tearDown(): void
    {
        Hash::swap($this->app['hash']);
        Mockery::close();

        parent::tearDown();
    }

    public function test_the_reset_password_throws_exception_when_user_has_no_password(): void
    {
        // Arrange
        $user = FakeUserReset::makeWithPassword(null);
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $service = $this->makeAuthService($userRepository);
        $caughtException = null;

        // Act
        try {
            $service->resetPassword([
                'email' => 'user@example.com',
                'password' => 'new-password',
            ]);
        } catch (ApiException $exception) {
            $caughtException = $exception;
        }

        // Assert
        if (! $caughtException) {
            $this->fail('Expected ApiException was not thrown.');
        }

        $this->assertSame('هذا الحساب ليس لديه كلمة مرور ليتم اعادة تعينها', $caughtException->getMessage());
        $this->assertNull($user->updatedAttributes);
    }

    public function test_the_reset_password_throws_exception_when_new_password_same_as_current(): void
    {
        // Arrange
        $user = FakeUserReset::makeWithPassword('current-hash');
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $service = $this->makeAuthService($userRepository);
        $caughtException = null;

        Hash::shouldReceive('check')
            ->once()
            ->with('new-password', Mockery::type('string'))
            ->andReturn(true);

        Hash::shouldReceive('make')->never();

        // Act
        try {
            $service->resetPassword([
                'email' => 'user@example.com',
                'password' => 'new-password',
            ]);
        } catch (ApiException $exception) {
            $caughtException = $exception;
        }

        // Assert
        if (! $caughtException) {
            $this->fail('Expected ApiException was not thrown.');
        }

        $this->assertSame('يرجى اختيار كلمة مرور مختلفة عن الكلمة الحالية', $caughtException->getMessage());
        $this->assertNull($user->updatedAttributes);
    }

    public function test_the_reset_password_updates_password_when_new_password_is_different(): void
    {
        // Arrange
        $user = FakeUserReset::makeWithPassword('current-hash');
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $service = $this->makeAuthService($userRepository);

        Hash::shouldReceive('check')
            ->once()
            ->with('new-password', Mockery::type('string'))
            ->andReturn(false);

        Hash::shouldReceive('make')
            ->once()
            ->with('new-password')
            ->andReturn('hashed-new-password');
            

        // Act
        $result = $service->resetPassword([
            'email' => 'user@example.com',
            'password' => 'new-password',
        ]);

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertSame('hashed-new-password', $user->password);
        $this->assertSame(['password' => 'hashed-new-password'], $user->updatedAttributes);
    }

    private function makeAuthService(UserRepository $userRepository): AuthService
    {
        $otpRepo = Mockery::mock(OtpCodesRepository::class);
        $failedRepo = Mockery::mock(FailedLoginRepository::class);
    
        return new AuthService($userRepository, $otpRepo, $failedRepo);
    }

}

class FakeUserReset extends User
{
    public ?array $updatedAttributes = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function makeWithPassword(?string $password): self
{
    $u = new self();
    $u->attributes['password'] = $password; // bypass casts/mutators
    return $u;
}

    public function update(array $attributes = [], array $options = []): bool
{
    $this->updatedAttributes = $attributes;

    foreach ($attributes as $k => $v) {
        if ($k === 'password') {
            $this->attributes['password'] = $v; // bypass casts/mutators
        } else {
            $this->setAttribute($k, $v);
        }
    }

    return true;
}

}
