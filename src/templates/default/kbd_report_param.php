<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\BlagoBot\view\InlineButton;

$back = new InlineButton($report);
$keyboard = [
    [$back->buttonData('🔙 Назад')]
];

foreach ($values as $value) {
    $button = new InlineButton($value, $param);
    $keyboard[] = [$button->buttonData()];
}

echo serialize(new InlineKeyboardMarkup($keyboard));
