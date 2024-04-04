<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\view\MenuView;
use losthost\telle\Bot;

class CommandStart extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText() && preg_match('/^\/start$/i', $message->getText())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        MenuView::show(Bot::param('topmenu_id', null), 0);
        return true;
    }
}
