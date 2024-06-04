<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

$keyboard = [];

$omsu_roles = [
    'head' => 'глава',
    'vicehead' => 'замглавы'
];

foreach ($bindings as $binding) {
    $keyboard[] = [['text' => "➖ $binding[1] ({$omsu_roles[$binding[2]]})", 'callback_data' => "edit_unbind{$binding[2]}_{$user->id}_$binding[0]"]];
}

$keyboard[] = [['text' => '➕ Глава', 'callback_data' => "edit_head_$user->id"], ['text' => '➕ Замглавы', 'callback_data' => "edit_vicehead_$user->id"]];
$keyboard[] = [['text' => '🔙 Назад', 'callback_data' => "edit_start_$user->id"]];

echo serialize(new InlineKeyboardMarkup($keyboard));