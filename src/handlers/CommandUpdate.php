<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BotView\BotView;
use losthost\telle\Bot;
use losthost\BlagoBot\handlers\MessageFile;

class CommandUpdate extends AbstractHandlerMessage {
    //put your code here
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText() && preg_match('/^\/update$/i', $message->getText())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        
        MessageFile::setPriority([]);
        $view->show('cmd_update');
        return true;
    }
}
