<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\user;

use function \losthost\BlagoBot\initBUser;

class PreMessage extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        global $b_user;
        initBUser();

        \losthost\BlagoBot\log_memory_usage();

        return $b_user->access_level == user::AL_UNKNOWN;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $view = new BotView(Bot::$api, Bot::$user->id, Bot::$language_code);
        $view->show('tpl_not_authorized');
        return true;
    }
}
