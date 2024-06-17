<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [ ['text' => '😎 Админ', 'callback_data' => "edit_admin_$user->id"],  ['text' => '🎥 Оператор', 'callback_data' => "edit_operator_$user->id"]], 
    [ ['text' => '👤 Пользователь МО', 'callback_data' => "edit_user_$user->id"] ],
    [ ['text' => '📩 Пользователь ОМСУ', 'callback_data' => "edit_restricted_$user->id"] ],
    [ ['text' => '🔙 Назад', 'callback_data' => "edit_start_$user->id"] ]
]));