<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\user;
use function losthost\BlagoBot\addBUser;
use function losthost\BlagoBot\showUser;

class MessageAuth extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText() && $message->getForwardFrom()) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        global $b_user;
        $view = new BotView(Bot::$api, Bot::$user->id, Bot::$language_code);
        
        if ($b_user->access_level != user::AL_ADMIN) {
            $view->show('tpl_admins_only');
            return true;
        }
        
        $originator = $message->getForwardFrom();
        $user = addBUser($originator->getId(), user::AL_UNKNOWN);
        $message_id = showUser($user);
        MessageFIO::setPriority(['user_id' => $user->id, 'message_id' => $message_id]);
        
        return true;
    }
}
