<?php

namespace App\Repository;
use App\Models\DialogueHistory;
use Telegram\Bot\Laravel\Facades\Telegram;

class DialogueHistoryRepository 
{

    public function deleteDialogueHistoryByTime()
    {
        $dialogHistories = DialogueHistory::all();
        if($dialogHistories){
            $today = new \DateTime(date("Y-m-d H:i:s"));
            foreach($dialogHistories as $dialogHistory){
                $diffDialogHistory = $dialogHistory->created_at->diff($today);
                if($diffDialogHistory->format("%H:%I") >= '00:19'){
                    Telegram::editMessageText([
                        'chat_id' => $dialogHistory->telegram_id,
                        'message_id' => $dialogHistory->message_id,
                        'text' => "С вами хотел(а) бы поговорить $dialogHistory->name"
                    ]);
                    $dialogHistory->delete();
                }
            }
        }
    }

    public function deleteDialogueHistoryByTelegramId($member, $messageId)
    {
        DialogueHistory::where('telegram_id', $member->telegram_id)
                        ->where('message_id', $messageId)
                        ->delete();
    }

}