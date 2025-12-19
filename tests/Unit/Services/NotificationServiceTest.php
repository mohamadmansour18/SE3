<?php

namespace Tests\Unit\Services;

use App\Repositories\Audit\NotificationRepository;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function test_getCitizenNotifications_returns_mapped_array_from_cache(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2025-01-01 12:00:00'));
        $citizenId = 15;

        $notificationOne = $this->makeNotification(1, [
            'title' => 'First Title',
            'body' => 'First Body',
        ], '2025-01-01 11:00:00');

        $notificationTwo = $this->makeNotification(2, [
            'title' => 'Second Title',
            'body' => 'Second Body',
        ], '2025-01-01 10:00:00');

        $repository = Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('getUserNotifications')
            ->once()
            ->with($citizenId)
            ->andReturn(new EloquentCollection([$notificationOne, $notificationTwo]));

        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $ttl, $callback) use ($citizenId): bool {
                return $key === "citizen:notifications:{$citizenId}"
                    && $ttl instanceof \DateTimeInterface
                    && is_callable($callback);
            })
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $service = new NotificationService($repository);

        // Act
        $result = $service->getCitizenNotifications($citizenId);

        // Assert
        $this->assertCount(2, $result);
        foreach ($result as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('body', $item);
            $this->assertArrayHasKey('date', $item);
            $this->assertIsString($item['date']);
            $this->assertNotSame('', $item['date']);
        }
    }

    public function test_getCitizenNotifications_uses_cache_and_does_not_call_repository_when_cache_returns_value(): void
    {
        // Arrange
        $citizenId = 20;
        $cached = [
            ['id' => 1, 'title' => 'Cached', 'body' => 'Body', 'date' => 'just now'],
        ];

        $repository = Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('getUserNotifications')->never();

        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $ttl): bool {
                return $key === 'citizen:notifications:20'
                    && $ttl instanceof \DateTimeInterface;
            })
            ->andReturn($cached);

        $service = new NotificationService($repository);

        // Act
        $result = $service->getCitizenNotifications($citizenId);

        // Assert
        $this->assertSame($cached, $result);
    }

    public function test_getCitizenNotifications_sets_empty_title_and_body_when_missing_in_data(): void
    {
        // Arrange
        Carbon::setTestNow(Carbon::parse('2025-01-01 12:00:00'));
        $citizenId = 30;

        $notification = $this->makeNotification(3, [], '2025-01-01 11:30:00');

        $repository = Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('getUserNotifications')
            ->once()
            ->with($citizenId)
            ->andReturn(new EloquentCollection([$notification]));

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $service = new NotificationService($repository);

        // Act
        $result = $service->getCitizenNotifications($citizenId);

        // Assert
        $this->assertSame('', $result[0]['title']);
        $this->assertSame('', $result[0]['body']);
        $this->assertIsString($result[0]['date']);
        $this->assertNotSame('', $result[0]['date']);
    }

    public function test_getCitizenNotifications_builds_correct_cache_key_per_citizen(): void
    {
        // Arrange
        $keys = [];

        $repository = Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('getUserNotifications')->never();

        Cache::shouldReceive('remember')
            ->twice()
            ->andReturnUsing(function ($key, $ttl, $callback = null) use (&$keys) {
                $keys[] = $key;
                return [];
            });

        $service = new NotificationService($repository);

        // Act
        $resultOne = $service->getCitizenNotifications(1);
        $resultTwo = $service->getCitizenNotifications(2);

        // Assert
        $this->assertSame([], $resultOne);
        $this->assertSame([], $resultTwo);
        $this->assertSame(['citizen:notifications:1', 'citizen:notifications:2'], $keys);
    }

    private function makeNotification(int $id, array $data, string $createdAt): object
    {
        $notification = new class {
            public int $id;
            public array $data;
            public string $created_at;
        };

        $notification->id = $id;
        $notification->data = $data;
        $notification->created_at = $createdAt;

        return $notification;
    }
}
