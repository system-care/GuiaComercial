<?php

namespace App\Mail;

use App\Models\RegistrationOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly RegistrationOtp $otp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de verificação — Guia Comercial',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
        );
    }
}
