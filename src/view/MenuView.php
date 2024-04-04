<?php

namespace losthost\BlagoBot\view;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\menu;

class MenuView {

    static public function show(int $id, int $message_id) {
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $menu = new menu(['id' => $id], true);
        if ($menu->isNew()) {
            $view->show('tpl_no_menu', $message_id);
        } else {
            $view->show('tpl_menu', 'kbd_menu', ['menu' => $menu], $message_id);
        }
    }
    
}
