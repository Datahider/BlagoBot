<?php

namespace losthost\BlagoBot;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\user;

function showNoUser(user $user) {
    $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
    $view->show('tpl_info', null, ['type' => 'error', 'text' => 'Не найден пользователь с id '. $user->id]);
}

function showUser(user $user, int $message_id = null) {
    $bindings = $user->getBindings();
    $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
    $view->show('tpl_user_edit', 'kbd_user_edit', ['user' => $user, 'bindings' => $bindings], $message_id);
}

    
