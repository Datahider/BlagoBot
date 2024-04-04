<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback; 
use losthost\BotView\BotView;
use losthost\telle\Bot;
use losthost\BlagoBot\data\user;

use function \losthost\BlagoBot\addBUser;

class CallbackAddUser extends AbstractHandlerCallback {
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        global $b_user;
        if ($b_user->is_admin && preg_match("/^add_as_/", $callback_query->getData())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $m = [];
        preg_match("/^add_as_(\w+)_(\d+)/", $callback_query->getData(), $m);
        $view = new BotView(Bot::$api, Bot::$user->id, Bot::$language_code);
        
        switch ($m[1]) {
            case 'admin': 
                addBUser($m[2], true);
                $view->show('tpl_added_admin', null, [], $callback_query->getMessage()->getMessageId());
                break;
            case 'user':
                addBUser($m[2], false);
                $view->show('tpl_added_user', null, [], $callback_query->getMessage()->getMessageId());
                break;
            case 'none':
                $user = new user(['tg_user' => $m[2]], true);
                if (!$user->isNew()) {
                    $user->delete();
                }
                $view->show('tpl_added_none', null, [], $callback_query->getMessage()->getMessageId());
                break;
            default:
                $view->show('tpl_unknown_error');
        }
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
        
    }
}
