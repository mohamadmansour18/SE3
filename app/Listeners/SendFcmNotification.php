<?php

namespace App\Listeners;

use App\Events\NotificationRequested;
use App\Helpers\TextHelper;
use App\Models\User;
use App\Services\Bridge\FirebaseNotificationChannel;
use App\Services\Bridge\SimpleTextNotification;
use App\Services\Bridge\TransactionCreatedNotification;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendFcmNotification
{
    public int $tries = 2;
    public int $backoff = 10;
    /**
     * Create the event listener.
     */
    public function __construct(
        protected FirebaseNotificationService $fcm
    )
    {}

    /**
     * Handle the event.
     */
    public function handle(NotificationRequested $event): void
    {
        $users = User::whereIn('id', $event->userIds)
            ->with('fcmTokens')
            ->get();

//        foreach ($users as $user)
//        {
//            try {
//                $tokens = $user->fcmTokens->pluck('token')->all();
//
//                if (empty($tokens)) {
//                    Log::info(TextHelper::fixBidi("المستخدم {$user->id} لا يملك FCM Tokens"));
//                    continue;
//                }
//
//                $this->fcm->send($event->title , $event->body , $tokens);
//            } catch (\Throwable $e) {
//                Log::error(TextHelper::fixBidi("فشل إرسال إشعار FCM للمستخدم {$user->id}: " . $e->getMessage()));
//            }
//        }

        try {

            //Implementor
            $channel = new FirebaseNotificationChannel($this->fcm);

            //Abstraction
            $notification = new SimpleTextNotification($channel , $event->body , $event->body);
            //$notification = new TransactionCreatedNotification($channel , $event->transaction);

            //bridge inside this
            $notification->sendToUsers($users);


        }catch(\Throwable $e)
        {
            Log::error(TextHelper::fixBidi("فشل إرسال إشعار FCM للمستخدم " . $e->getMessage()));
        }

    }
}
