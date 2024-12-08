<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MerchantCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mail;
    public $password;
    public $merchant_name;
    public $branch;

    /**
     * Create a new message instance.
     */
    public function __construct($mail, $password, $merchant_name, $branch)
    {
        $this->mail = $mail;
        $this->password = $password;
        $this->merchant_name = $merchant_name;
        $this->branch = $branch;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Merchant Credentials for Trendz Studio',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.merchant-credential',
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
