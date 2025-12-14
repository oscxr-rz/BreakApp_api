<?php

namespace App\Mail\actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class enviarTicket extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $pdfPath;

    public function __construct($ticket, $pdfPath)
    {
        $this->ticket = $ticket;
        $this->pdfPath = 'storage/'.$pdfPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu ticket de compra',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('ticket.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
