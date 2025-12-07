<?php

namespace App\Listeners;

use App\Events\FcmNotificationRequested;
use App\Models\User;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreFcmNotification implements ShouldQueue
{
    public int $tries = 2;
    public int $backoff = 10;

    /**
     * Handle the event.
     */
    public function handle(FcmNotificationRequested $event): void
    {
        $users = User::whereIn('id', $event->userIds)->get();

        foreach ($users as $user) {
            $user->notify(new FcmNotification($event->title, $event->body));
        }
    }
}
