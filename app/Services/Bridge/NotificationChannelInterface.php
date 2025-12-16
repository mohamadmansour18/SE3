<?php

namespace App\Services\Bridge;

use App\Models\User;

interface NotificationChannelInterface
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): void;
}
