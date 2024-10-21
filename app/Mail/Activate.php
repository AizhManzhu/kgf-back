<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class Activate extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $message)
    {
        $this->message = $message;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Здравствуйте,
С благодарностью подтверждаем Ваше участие в Kazakhstan Marketing Conference,
который состоится 26 января 2023 года')
            ->from(env('MAIL_FROM_ADDRESS'), 'Kazakhstan Marketing Conference')
            ->markdown('activate-mail');
    }
}
