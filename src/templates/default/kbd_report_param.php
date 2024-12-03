<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\BlagoBot\view\InlineButton;
use losthost\BlagoBot\params\ParamValue;


$keyboard = [];

foreach ($values as $value) {
    $button = new InlineButton($value, $param);
    $keyboard[] = [$button->buttonData()];
}

if ($param->isMultipleChoice()) {
    $value_reverse = new ParamValue('Инвертировать выбор', '<=reverse=>');
    $reverse = new InlineButton($value_reverse, $param);
    $keyboard[] = [$reverse->buttonData('🔀 Инвертировать выбор')];
}
$back = new InlineButton($report);
$keyboard[] = [$back->buttonData('🔙 Назад')];

echo serialize(new InlineKeyboardMarkup($keyboard));
