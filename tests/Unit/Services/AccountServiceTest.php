<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;
use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use App\Services\AccountService;
use App\Services\Strategy\AccountOpeningResult;
use App\Services\Strategy\AccountOpeningStrategy;
use App\Services\Strategy\AccountOpeningStrategyFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class AccountServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_the_get_accounts_returns_mapped_array_with_expected_fields(): void
    {
        // Arrange
        $userId = 9;

        $accountOne = FakeAccountServiceAccount::make([
            'id' => 1,
            'name' => 'First',
            'account_number' => 'ACC-1',
            'description' => 'Primary',
            'type' => 'توفير',
            'status' => AccountStatus::ACTIVE->value,
            'balance' => '150.00',
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
        ]);

        $accountTwo = FakeAccountServiceAccount::make([
            'id' => 2,
            'name' => 'Second',
            'account_number' => 'ACC-2',
            'description' => 'Secondary',
            'type' => 'جاري',
            'status' => AccountStatus::FROZEN->value,
            'balance' => '75.50',
            'created_at' => Carbon::parse('2024-01-02 11:30:00'),
        ]);

        $accountRepository = Mockery::mock(AccountRepository::class);
        $accountRepository->shouldReceive('findAccountByUserId')
            ->once()
            ->with($userId)
            ->andReturn(new Collection([$accountOne, $accountTwo]));

        $factory = Mockery::mock(AccountOpeningStrategyFactory::class);
        $service = new AccountService($accountRepository, $factory);

        // Act
        $result = $service->getAccounts($userId);

        // Assert
        $this->assertCount(2, $result);

        foreach ($result as $account) {
            $this->assertArrayHasKey('id', $account);
            $this->assertArrayHasKey('name', $account);
            $this->assertArrayHasKey('account_number', $account);
            $this->assertArrayHasKey('description', $account);
            $this->assertArrayHasKey('type', $account);
            $this->assertArrayHasKey('status', $account);
            $this->assertArrayHasKey('balance', $account);
            $this->assertArrayHasKey('created_at', $account);
        }

        $this->assertSame('2024-01-01', $result[0]['created_at']);
        $this->assertSame('2024-01-02', $result[1]['created_at']);
    }

    public function test_the_update_account_throws_when_account_is_closed(): void
    {
        // Arrange
        $userId = 3;
        $accountId = 20;

        $account = FakeAccountServiceAccount::make([
            'id' => $accountId,
            'status' => AccountStatus::CLOSED->value,
        ]);

        $accountRepository = Mockery::mock(AccountRepository::class);
        $accountRepository->shouldReceive('findUserAccountById')
            ->once()
            ->with($userId, $accountId)
            ->andReturn($account);

        $accountRepository->shouldReceive('updateAccountFields')->never();

        $factory = Mockery::mock(AccountOpeningStrategyFactory::class);
        $service = new AccountService($accountRepository, $factory);

        // Assert (simple)
        try {
            $service->updateAccount($userId, $accountId, ['name' => 'Updated']);
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException $exception) {
            $this->assertSame('لا يمكن اجراء تعديلات على حساب مغلق', $exception->getMessage());
            $this->assertSame(422, $exception->getCode());
        }
    }

    public function test_the_update_account_updates_fields_when_account_is_not_closed(): void
    {
        // Arrange
        $userId = 4;
        $accountId = 30;
        $attributes = ['name' => 'New Name'];

        $account = FakeAccountServiceAccount::make([
            'id' => $accountId,
            'status' => AccountStatus::ACTIVE->value,
        ]);

        $accountRepository = Mockery::mock(AccountRepository::class);
        $accountRepository->shouldReceive('findUserAccountById')
            ->once()
            ->with($userId, $accountId)
            ->andReturn($account);

        $accountRepository->shouldReceive('updateAccountFields')
            ->once()
            ->with($account, $attributes)
            ->andReturn($account);

        $factory = Mockery::mock(AccountOpeningStrategyFactory::class);
        $service = new AccountService($accountRepository, $factory);

        // Act
        $result = $service->updateAccount($userId, $accountId, $attributes);

        // Assert
        $this->assertSame($account, $result);
    }

    public function test_the_get_accounts_calls_repository_with_user_id(): void
    {
        // Arrange
        $userId = 12;

        $accountRepository = Mockery::mock(AccountRepository::class);
        $accountRepository->shouldReceive('findAccountByUserId')
            ->once()
            ->with($userId)
            ->andReturn(new Collection());

        $factory = Mockery::mock(AccountOpeningStrategyFactory::class);
        $service = new AccountService($accountRepository, $factory);

        // Act
        $result = $service->getAccounts($userId);

        // Assert
        $this->assertSame([], $result);
    }
}

class FakeAccountServiceAccount extends Account
{
    public static function make(array $attributes = []): self
    {
        $account = new self();

        foreach ($attributes as $key => $value) {
            $account->attributes[$key] = $value;
        }

        return $account;
    }
}
