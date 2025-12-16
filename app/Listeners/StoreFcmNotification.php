<?php

namespace App\Listeners;

use App\Events\NotificationRequested;
use App\Models\User;
use App\Notifications\FcmNotification;
use App\Services\Bridge\DatabaseNotificationChannel;
use App\Services\Bridge\SimpleTextNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreFcmNotification implements ShouldQueue
{
    public int $tries = 2;
    public int $backoff = 10;

    /**
     * Handle the event.
     */
    public function handle(NotificationRequested $event): void
    {
        $users = User::whereIn('id', $event->userIds)->get();

//        foreach ($users as $user) {
//            $user->notify(new FcmNotification($event->title, $event->body));
//        }

        $channel = new DatabaseNotificationChannel();
        $notification = new SimpleTextNotification($channel , $event->title , $event->body);
        $notification->sendToUsers($users);

    }
}
