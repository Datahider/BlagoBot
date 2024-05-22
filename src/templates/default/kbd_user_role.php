<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [ ['text' => '😎 Админ', 'callback_data' => "edit_admin_$user->id"], ['text' => '👤 Пользователь', 'callback_data' => "edit_user_$user->id"] ], 
    [ ['text' => '📩 Получатель рассылки', 'callback_data' => "edit_restricted_$user->id"] ],
    [ ['text' => '🔙 Назад', 'callback_data' => "edit_start_$user->id"] ]
]));