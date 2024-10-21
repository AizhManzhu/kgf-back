<?php

namespace App\Jobs;

use App\Mail\Activate;
use App\Mail\CommonMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;

class CommonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $subject;
    protected $name;
    protected $message;
    protected $attachment;

    /*
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $subject, $name, $message, $attachment = null)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->name = $name;
        $this->message = $message;
        $this->attachment = $attachment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Mail::to($this->email)
                ->send(new CommonMail($this->subject, $this->name, $this->message));
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }
}
