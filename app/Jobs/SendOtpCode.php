<?php

namespace App\Jobs;

use App\Mail\SendOtpMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOtpCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2 ;
    public int $backoff = 5 ;
    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $email,
        public string $name,
        public string $code,
        public string $purpose
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new SendOtpMail($this->code , $this->name , $this->purpose));
    }
    public function failed(\Throwable $exception): void
    {
        Log::error('Send OTP email job failed for user : ' . $this->email . ' || has name : ' . $this->name . ' || purpose is : ' . $this->purpose);
        Log::error($exception->getMessage());
    }
}
