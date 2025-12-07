<?php

namespace App\Mail;

use App\Enums\OtpCodePurpose;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $code,
        public string $name,
        public string $purpose
    )
    {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if($this->purpose === OtpCodePurpose::Verification->value) {
            return new Envelope(
                subject: 'رمز التحقق الخاص بك لتأكيد بريدك الالكتروني',
            );
        } else {
            return new Envelope(
                subject: 'رمز التحقق الخاص بك لاعادة تعين كلمة المرور'
            );
        }
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
