<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class CommonMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $message;
    public $subject;
    public $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $name, $message, $attachment = null)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->name = $name;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->subject)
            ->from(env('MAIL_FROM_ADDRESS'), $this->name)
            ->markdown('common-mail');
        if ($this->attachment != null)
            $mail->attachment($this->attachment);
        return $mail;
    }
}
