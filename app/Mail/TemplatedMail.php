<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $body;

    public string $bodyHtml;

    public string $key;

    public function __construct(string $emailSubject, string $body, string $key)
    {
        $this->subject = $emailSubject;
        $this->body = $body;
        $this->bodyHtml = self::linkify($body);
        $this->key = $key;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.templated');
    }

    /**
     * Escape the plain-text body, then turn URLs into anchor tags so mail
     * clients always linkify them correctly (escaped &amp; in href is valid
     * HTML and decodes back to & on click, keeping signed URLs intact).
     */
    private static function linkify(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace_callback(
            '/https?:\/\/[^\s<]+/u',
            fn (array $match) => '<a href="'.$match[0].'" style="color:#007774;">'.$match[0].'</a>',
            $escaped
        );
    }
}
