<?php

namespace App\Services\Bridge;

use App\Helpers\TextHelper;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly FirebaseNotificationService $fcm,
    ) {}

    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = $user->fcmTokens->pluck('token')->all();

        if (empty($tokens)) {
            Log::info(TextHelper::fixBidi("المستخدم {$user->id} لا يملك FCM Tokens"));
            return;
        }

        $this->fcm->send($title, $body, $tokens , $data);
    }
}
