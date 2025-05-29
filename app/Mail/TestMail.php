<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $attachmentPaths;

    /**
     * Create a new message instance.
     *
     * @param array $data 
     * @param array $attachmentPaths 
     */
    public function __construct($data, $attachmentPaths)
    {
        $this->data = $data;
        $this->attachmentPaths = $attachmentPaths;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data['subject'], 
            from: new Address("supremebart45@gmail.com", "Test Mailer"), // Use a more descriptive sender name
        );
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'test-mail', 
    //         with: [
    //             'data' => $this->data, 
    //         ],
    //     );
    // }

    public function build()
    {
        $email = $this->subject($this->data['subject'])
            ->view('test-mail')
            ->with('data', $this->data);

        foreach ($this->attachmentPaths as $path) {
            if (file_exists($path)) {
                $email->attach($path, [
                    'as' => basename($path)
                ]);
            } else {
                \Log::warning("Attempted to attach non-existent file: " . $path);
            }
        }

        return $email;
    }
}
