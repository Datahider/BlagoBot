<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;

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
        
        if (!$b_user->is_admin) {
            $view->show('tpl_admins_only');
            return true;
        }
        
        $originator = $message->getForwardFrom();
        $view->show('tpl_auth', 'kbd_auth', ['originator' => $originator]);
        
        return true;
    }
}
