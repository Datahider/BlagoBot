<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\BlagoBot\view\InlineButton;
use losthost\telle\Bot;

$lastmenu_id = Bot::$session->mode;
$topmenu_id = Bot::param('topmenu_id', null);

$keyboard = [
    [['text' => '🔙 Назад', 'callback_data' => 'submenu_'. $lastmenu_id], ['text' => '🏠 В начало', 'callback_data' => 'submenu_'. $topmenu_id]]
];

if ($report->hasNoParams()) {
    $keyboard[] = [['text' => 'Сформировать', 'callback_data' => 'makereport_'. $report->id]];
} elseif ($report->isFastSelect()) {
    foreach ($report_param_values as $value) {
        $button = new InlineButton($value, $report_params[0]);
        $keyboard [] = [$button->buttonData()];
    }
} elseif ($report->hasOneParam()) {
    foreach ($report_param_values as $value) {
        $button = new InlineButton($value, $report_params[0]);
        $keyboard [] = [$button->buttonData()];
    }
    $keyboard[] = [['text' => 'Сформировать', 'callback_data' => 'makereport_'. $report->id]];
} else {
    foreach ($report_params as $param) {
        $button = new InlineButton($param);
        $keyboard [] = [$button->buttonData()];
    }
    $keyboard[] = [['text' => 'Сформировать', 'callback_data' => 'makereport_'. $report->id]];
}

echo serialize(new InlineKeyboardMarkup($keyboard));
