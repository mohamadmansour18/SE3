<?php

namespace App\Services\Bridge;

use App\Models\User;
use App\Notifications\FcmNotification;

class DatabaseNotificationChannel implements NotificationChannelInterface
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $user->notify(new FcmNotification($title, $body));
    }
}
