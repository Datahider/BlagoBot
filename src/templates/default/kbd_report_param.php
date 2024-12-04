<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\BlagoBot\view\InlineButton;
use losthost\BlagoBot\params\ParamValue;
use losthost\BlagoBot\params\AbstractParamDescription;
use losthost\BlagoBot\data\report_param_value;


$keyboard = [];

foreach ($values as $value) {
    $button = new InlineButton($value, $param);
    $keyboard[] = [$button->buttonData()];
}

if ($param->isMultipleChoice() && is_a($param, AbstractParamDescription::class)) {
    $value_reverse = new ParamValue('Инвертировать выбор', '<=reverse=>');
    $reverse = new InlineButton($value_reverse, $param);
    $keyboard[] = [$reverse->buttonData('🔀 Инвертировать выбор')];
} elseif ($param->isMultipleChoice()) {
    $value_reverse = new report_param_value(['id' => 999999999], true);
    $reverse = new InlineButton($value_reverse, $param);
    $keyboard[] = [$reverse->buttonData('🔀 Инвертировать выбор')];
}
$back = new InlineButton($report);
$keyboard[] = [$back->buttonData('🔙 Назад')];

echo serialize(new InlineKeyboardMarkup($keyboard));
