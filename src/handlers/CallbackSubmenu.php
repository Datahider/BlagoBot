<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\view\MenuView;
use losthost\telle\Bot;
use losthost\BlagoBot\data\menu;

class CallbackSubmenu extends AbstractHandlerCallback {
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        if (preg_match('/^submenu_/', $callback_query->getData())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $m = [];
        preg_match('/^submenu_(\d+)$/', $callback_query->getData(), $m);
        
        $menu = new menu(['id' => $m[1]]);
        $view = new MenuView($menu);
        $view->show($callback_query->getMessage()->getMessageId());
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
}
