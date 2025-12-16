<?php

namespace App\Services\Bridge;

use App\Models\User;

class SimpleTextNotification extends BaseNotification
{
    public function __construct(
        $channel,
        private readonly string $title,
        private readonly string $body,
    ) {
        parent::__construct($channel);
    }

    protected function buildMessage(User $user): array
    {
        return [$this->title, $this->body, []];
    }
}
