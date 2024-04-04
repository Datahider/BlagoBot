<?php

use losthost\telle\Bot;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

$topmenu_id = Bot::param('topmenu_id', null);

if ( $topmenu_id == $menu->id) {
    $keyboard = [];
} else {
    $keyboard = [
        [['text' => '🔙 Назад', 'callback_data' => 'submenu_'. $menu->parent], ['text' => '🏠 В начало', 'callback_data' => 'submenu_'. $topmenu_id]]
    ];
}

foreach ($menu->getChildren() as $sub_menu) {
    $keyboard[] = [['text' => $sub_menu->button_text, 'callback_data' => 'submenu_'. $sub_menu->id]];
}

echo serialize(new InlineKeyboardMarkup($keyboard));
