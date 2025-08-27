<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;

use function losthost\BlagoBot\__;
use function \losthost\BlagoBot\sendMessageWithRetry;

class CommandReset extends AbstractHandlerMessage {
    
    #[\Override]
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        global $b_user;
        
        $b_user->ai_context_starts = date_create();
        $b_user->write();
        
        sendMessageWithRetry(Bot::$chat->id, __('Контекст работы с ИИ-ассистентом сброшен.'), null);
        return true;
    }

    #[\Override]
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText() && preg_match('/^\/reset$/i', $message->getText())) {
            return true;
        }
        return false;
    }
}
