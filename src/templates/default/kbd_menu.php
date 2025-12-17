<?php

use losthost\telle\Bot;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\BlagoBot\view\InlineButton;
use losthost\BlagoBot\data\report;

$topmenu_id = Bot::param('topmenu_id', null);

if ( $topmenu_id == $menu->id) {
    $keyboard = [];
} else {
    $keyboard = [
        [['text' => '🔙 Назад', 'callback_data' => 'submenu_'. $menu->parent], ['text' => '🏠 В начало', 'callback_data' => 'submenu_'. $topmenu_id]]
    ];
}

foreach ($submenu as $sub_menu) {
    switch ($sub_menu->type) {
        case 'submenu':
            $button = new InlineButton($sub_menu);
            break;
        case 'report':
            $button = new InlineButton(new report(['id' => $sub_menu->subtype_id]));
            break;
        case 'link':
            $button = new InlineButton($sub_menu);
            break;
        default:
            throw new Exception('Unknown menu type.');
    }
    $keyboard[] = [$button->buttonData()];
}

echo serialize(new InlineKeyboardMarkup($keyboard));
