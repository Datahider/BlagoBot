<?php

namespace losthost\BlagoBot\view;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\menu;
use Exception;

class MenuView {

    protected $menu;
    
    public function __construct(menu $menu) {
        $this->menu = $menu;
    }
    
    public function show(int $message_id) {
        
        if (!$this->menu->description) {
            throw new Exception('There is no description for this menu.');
        }
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        if ($this->menu->isNew()) {
            $view->show('tpl_no_menu', null, [], $message_id);
        } else {
            $view->show('tpl_menu', 'kbd_menu', $this->viewData(), $message_id);
        }
        
        Bot::$session->set('command', null);
        Bot::$session->set('mode', $this->menu->id);
    }
    
    protected function viewData() {
        
        global $b_user;
        
        $data['menu'] = $this->menu;
        $data['submenu'] = $this->menu->getChildren($b_user->access_level);
        return $data;
    }
}
