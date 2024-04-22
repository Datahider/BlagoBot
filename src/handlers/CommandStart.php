<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\view\MenuView;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\menu;

class CommandStart extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText() && preg_match('/^\/start$/i', $message->getText())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        global $b_user;
        
        switch ($b_user->access_level) {
            case 'admin':
            case 'user':
                $menu = new menu(['id' => Bot::param('topmenu_id', null)]);
                $view = new MenuView($menu);
                $view->show(0);

                Bot::$session->set('data', []);
                break;
            case 'restricted': 
                $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
                $view->show('tpl_receive_only');
        }

        return true;
    }
}
