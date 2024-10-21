<?php

namespace App\Jobs;

use App\Models\TelegramHistory;
use App\Services\ImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;

class ActivateTelegramMailing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $email string
     */
    protected $telegramId;

    /**
     * @var $text string
     */
    protected $text;

    /**
     * @var $name array
     */
    protected $baseKeyboard;

    protected $eventMemberId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($telegramId, $text, $baseKeyboard, $eventMemberId = null)
    {
        $this->telegramId = $telegramId;
        $this->text = $text;
        $this->baseKeyboard = $baseKeyboard;
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
            TelegramBot::sendMessage([
                'chat_id' => $this->telegramId,
                'parse_mode' => 'html',
                'text' => $this->text,
                'reply_markup' => $this->baseKeyboard,
            ]);
            TelegramBot::sendMessage([
                'chat_id' => '316204431',
                'parse_mode' => 'html',
                'text' => $this->text,
                'reply_markup' => $this->baseKeyboard,
            ]);
            if ($this->eventMemberId != null) {
                $path = storage_path('app/public/uploads/' . $this->eventMemberId . ".jpeg");
                if (!file_exists($path)) {
                    Telegram::sendPhoto([
                        'chat_id' => $this->telegramId,
                        'photo' => new InputFile($path),
                        'reply_markup' => $this->baseKeyboard,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
}
