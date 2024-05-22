<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [ ['text' => '🎭 Роль', 'callback_data' => "edit_role_$user->id"], ['text' => '🪢 Привязки', 'callback_data' => "edit_bindings_$user->id"], ['text' => '🗑 Удалить', 'callback_data' => "edit_delete_$user->id"] ]
]));