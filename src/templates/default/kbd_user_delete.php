<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [ ['text' => '🚮 Да, удалить', 'callback_data' => "edit_confirmdelete_$user->id"],  ['text' => '✋ Нет, не удалять', 'callback_data' => "edit_start_$user->id"]]
]));