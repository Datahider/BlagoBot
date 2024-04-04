<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;

use function \losthost\BlagoBot\initBUser;

class PreCallback extends AbstractHandlerCallback {
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        global $b_user;
        initBUser();
        return !$b_user->is_authorized;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        global $b_user;
        $view = new BotView(Bot::$api, Bot::$user->id, Bot::$language_code);
        $view->show('tpl_not_authorized', null, ['code' => $b_user->auth_code]);
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
    
}
