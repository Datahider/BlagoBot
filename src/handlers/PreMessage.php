<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\BlagoBot\initBUser;

class PreMessage extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        global $b_user;
        initBUser();
        return !$b_user->is_authorized;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        global $b_user;
        $view = new BotView(Bot::$api, Bot::$user->id, Bot::$language_code);
        $view->show('tpl_not_authorized', null, ['code' => $b_user->auth_code]);
        return true;
    }
}
