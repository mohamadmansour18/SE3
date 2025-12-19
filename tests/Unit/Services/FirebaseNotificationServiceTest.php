<?php

namespace Tests\Unit\Services;

use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class FirebaseNotificationServiceTest extends TestCase
{
    private string $credentialsPath;
    private string $projectId = 'test-project-123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->credentialsPath = base_path('tests/fixtures/fcm_credentials_test.json');
        $directory = dirname($this->credentialsPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents(
            $this->credentialsPath,
            json_encode(['project_id' => $this->projectId])
        );

        config(['services.fcm.credentials_file' => 'tests/fixtures/fcm_credentials_test.json']);
    }

    protected function tearDown(): void
    {
        if (is_file($this->credentialsPath)) {
            unlink($this->credentialsPath);
        }

        Mockery::close();

        parent::tearDown();
    }

    public function test_send_returns_zero_success_and_failed_when_tokens_empty(): void
    {
        // Arrange
        Cache::shouldReceive('remember')->never();
        Http::fake();
        Log::shouldReceive('warning')->never();

        $service = new FirebaseNotificationService();

        // Act
        $result = $service->send('Title', 'Body', []);

        // Assert
        $this->assertSame(['success' => 0, 'failed' => 0], $result);
        Http::assertSentCount(0);
    }

    public function test_send_sends_request_for_each_token_and_counts_success_and_failed_correctly(): void
    {
        // Arrange
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('access-token');

        Http::fakeSequence()
            ->push('', 200)
            ->push('bad', 400)
            ->push('', 200);

        Log::shouldReceive('warning')
            ->once()
            ->with('FCM send failed', Mockery::on(function (array $context): bool {
                return $context['token'] === 'token-2'
                    && $context['status'] === 400
                    && $context['body'] === 'bad';
            }));

        $service = new FirebaseNotificationService();
        $tokens = ['token-1', 'token-2', 'token-3'];
        $expectedUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        // Act
        $result = $service->send('Title', 'Body', $tokens);

        // Assert
        $this->assertSame(2, $result['success']);
        $this->assertSame(1, $result['failed']);

        Http::assertSentCount(3);
        Http::assertSent(function ($request) use ($expectedUrl) {
            return $request->url() === $expectedUrl
                && $request->hasHeader('Authorization', 'Bearer access-token');
        });
    }

    public function test_send_includes_data_in_payload_when_data_not_empty(): void
    {
        // Arrange
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('access-token');

        Http::fake([
            '*' => Http::response('', 200),
        ]);

        Log::shouldReceive('warning')->never();

        $service = new FirebaseNotificationService();
        $payloadData = ['key' => 'value'];

        // Act
        $service->send('Title', 'Body', ['token-1'], $payloadData);

        // Assert
        Http::assertSent(function ($request) use ($payloadData) {
            $payload = $request->data();

            return isset($payload['message']['data'])
                && $payload['message']['data'] === $payloadData
                && $request->hasHeader('Authorization', 'Bearer access-token');
        });
    }

    public function test_send_does_not_include_data_when_empty(): void
    {
        // Arrange
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('access-token');

        Http::fake([
            '*' => Http::response('', 200),
        ]);

        Log::shouldReceive('warning')->never();

        $service = new FirebaseNotificationService();

        // Act
        $service->send('Title', 'Body', ['token-1'], []);

        // Assert
        Http::assertSent(function ($request) {
            $payload = $request->data();

            return ! isset($payload['message']['data'])
                && $request->hasHeader('Authorization', 'Bearer access-token');
        });
    }
}
