<?php

namespace App\Services\Bridge;

use App\Models\User;

abstract class BaseNotification
{
    public function __construct(
        protected NotificationChannelInterface $channel,
    ){}

    public function sendToUserSingle(User $user): void
    {
        [$title, $body, $data] = $this->buildMessage($user);

        $this->channel->sendToUser($user, $title, $body, $data);
    }

    public function sendToUsers(iterable $users): void
    {
        foreach ($users as $user) {
            $this->sendToUserSingle($user);
        }
    }

    abstract protected function buildMessage(User $user): array;
}
