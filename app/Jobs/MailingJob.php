<?php

namespace App\Jobs;

use App\Mail\Activate;
use App\Mail\BotanSignUp;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $email string
     */
    protected $email;

    /**
     * @var $text string
     */
    protected $text;

    /**
     * @var $name string
     */
    protected $name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $text, $name)
    {
        $this->email = $email;
        $this->text = $text;
        $this->name = $name;
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
                ->send(new BotanSignUp($this->name, $this->text));
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }
}
