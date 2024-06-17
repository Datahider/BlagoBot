<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\view\MenuView;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\menu;
use losthost\BlagoBot\service\AccessChecker;
use losthost\BlagoBot\data\user;

class CommandStart extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText() && preg_match('/^\/start$/i', $message->getText())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {

        $menu_access = new AccessChecker([user::AL_ADMIN, user::AL_USER, user::AL_OPERATOR, user::AL_RESTRICTED]);
        
        if ($menu_access->isAllowed()) {
            $menu = new menu(['id' => Bot::param('topmenu_id', null)]);
            $view = new MenuView($menu);
            $view->show(0);

            Bot::$session->set('data', []);
        }
        return true;
    }
}
