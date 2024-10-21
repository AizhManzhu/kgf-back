<?php

namespace App\Jobs;

use App\Http\Controllers\Api\TelegramController;
use App\Models\TelegramHistory;
use App\Models\Competition;
use App\Repository\TelegramRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramMailing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $telegramId
     */
    protected $telegramId;

    /**
     * @var $message string
     */
    protected $message;

    /**
     * @var $imageName string
     */
    protected $imageName;

    /**
     * @var $imIn bool
     */
    protected $imIn;

    protected $templateId;

    protected $competitionId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($telegramId, $message, $imageName, $templateId = null)
    {
        $this->telegramId = $telegramId;
        $this->message = $message;
        $this->imageName = $imageName;
        $this->templateId = $templateId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $replyMarkup = (new TelegramRepository())->getBaseKeyboard();
        if (!empty($this->imageName)) {
            try {
                Telegram::sendPhoto([
                    'chat_id' => $this->telegramId,
                    'photo' => new InputFile(storage_path('app/public/uploads') . '/' . $this->imageName, $this->imageName),
                    'reply_markup' => $replyMarkup,
                ]);
            } catch (\Exception $e) {
                Log::debug($e->getTraceAsString());
            }
        }
        try {
            if($this->message!=null && !empty($this->message)) {
                $result = Telegram::sendMessage([
                    'parse_mode' => 'HTML',
                    'chat_id' => $this->telegramId,
                    'text' => (new TelegramRepository())->replaceDiv($this->message),
                    'reply_markup' => $replyMarkup,
                ]);
            }

//            TelegramHistory::setHistory($result->message_id, $result->from->id, $result->chat->id, $result->text);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

}
