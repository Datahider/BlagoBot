<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;

use function losthost\BlagoBot\__;
use function \losthost\BlagoBot\sendMessageWithRetry;

class CommandReset extends AbstractHandlerCommand {
    
    const COMMAND = 'reset';
    
    #[\Override]
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        global $b_user;
        
        $b_user->ai_context_starts = date_create();
        $b_user->write();
        
        sendMessageWithRetry(Bot::$chat->id, __('Контекст работы с ИИ-ассистентом сброшен.'), null);
        return true;
    }
}
