<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\data\user;
use losthost\BotView\BotView;
use losthost\telle\Bot;

use function \losthost\BlagoBot\initBUser;

class PreCallback extends AbstractHandlerCallback {
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        global $b_user;
        
        error_log("Got callback data: ". $callback_query->getData());
        initBUser();
        return $b_user->access_level == user::AL_UNKNOWN;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $view = new BotView(Bot::$api, Bot::$user->id, Bot::$language_code);
        $view->show('tpl_not_authorized');
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
    
}
