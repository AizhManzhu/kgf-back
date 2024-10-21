<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class SignUp extends Mailable
{
    use Queueable, SerializesModels;

    public $hash;
    public $name;
    public $message;
    public $eventMemberId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($id, $name, $message = '', $eventMemberId = null)
    {
        $this->hash =  $id."-".md5($id.env('TELEGRAM_BOT_TOKEN'));
        $this->name = $name;
        $this->message = $message;
        $this->eventMemberId = $eventMemberId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Регистрация на Kazakhstan Marketing Conference 2023')
            ->from(env('MAIL_FROM_ADDRESS'), 'Kazakhstan Marketing Conference')
            ->markdown('SignUpView');
    }
}
