<?php

namespace App\Jobs;

use App\Helpers\TextHelper;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2 ;
    public int $backoff = 10 ;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $userIds ,
        protected string $title ,
        protected string $body ,
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(FirebaseNotificationService $fcm): void
    {
        $users = User::whereIn('id' , $this->userIds)->with('fcmTokens')->get();

        foreach ($users as $user) {
            try {
                $tokens = $user->fcmTokens->pluck('token')->all();
                if(empty($tokens))
                {
                    Log::info(TextHelper::fixBidi("المستخدم {$user->id} لا يملك FCM Tokens"));
                    continue ;
                }
                $fcm->send($this->title, $this->body, $tokens);
            }catch (\Throwable $exception){
                Log::error(TextHelper::fixBidi("فشل إرسال إشعار للمستخدم {$user->id}: " . $exception->getMessage()));
            }
        }
    }
}
