<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $template;
    public array $data;

    public function __construct(string $subject, string $template, array $data = [])
    {
        $this->emailSubject = $subject;
        $this->template = $template;
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->template,
            with: $this->data,
        );
    }
}
