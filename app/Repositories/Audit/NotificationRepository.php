<?php

namespace App\Repositories\Audit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository
{
    public function getUserNotifications(int $citizenId): Collection|array
    {
        return DatabaseNotification::query()
            ->where('notifiable_id' , $citizenId)
            ->where('notifiable_type', 'App\\Models\\User')
            ->latest()
            ->get();
    }

}
