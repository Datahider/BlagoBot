<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\view\MenuView;
use losthost\telle\Bot;
use losthost\BlagoBot\data\menu;

class CommandStart extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText() && preg_match('/^\/start$/i', $message->getText())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $menu = new menu(['id' => Bot::param('topmenu_id', null)]);
        $view = new MenuView($menu);
        $view->show(0);

        Bot::$session->set('data', []);

        return true;
    }
}
