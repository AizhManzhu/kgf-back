<?php

namespace App\Jobs;

use App\Mail\SignUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VipMemberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $name;
    protected $email;
    protected $message;
    protected $eventMemberId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $name, $email, $message = '', $eventMemberId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
        $this->eventMemberId = $eventMemberId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Mail::to($this->email)->send(new SignUp($this->id, $this->name, $this->message, $this->eventMemberId));
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }
}
