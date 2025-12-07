<?php

namespace App\Jobs;

use App\Mail\FailedLoginMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FailedLogin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user ,
        public array $attemptDetails //ip / user_agent / occurred_at
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new FailedLoginMail($this->user , $this->attemptDetails));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Send security email job failed for user : ' . $this->user->email . ' || has name : ' . $this->user->name );
        Log::error($exception->getMessage());
    }
}
