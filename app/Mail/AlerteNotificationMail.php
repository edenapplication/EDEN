<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\RH\Notification;

class AlerteNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->sujet . ' - Eden RH',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerte_notification',
            with: [
                'notification' => $this->notification,
                'alerte' => $this->notification->alerte,
            ]
        );
    }
}