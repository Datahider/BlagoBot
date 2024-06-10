<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

$line1 = [ ['text' => '🎭 Роль', 'callback_data' => "edit_role_$user->id"] ];
if ($user->name) {
    $line1[] = ['text' => '🪢 Привязки', 'callback_data' => "edit_bindings_$user->id"];
}
 
$keyboard = [
    $line1,
    [ ['text' => '🗑 Удалить пользователя', 'callback_data' => "edit_delete_$user->id"], ['text' => '✅ Завершить редактирование', 'callback_data' => "edit_end_$user->id"] ]
];

echo serialize(new InlineKeyboardMarkup($keyboard));