<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [['text' => 'Админ', 'callback_data' => 'add_as_admin_'. $originator->getId()], ['text' => 'Удалить', 'callback_data' => 'add_as_none_'. $originator->getId()]],
    [['text' => 'Пользователь', 'callback_data' => 'add_as_user_'. $originator->getId()]],
    [['text' => 'Получатель рассылки', 'callback_data' => 'add_as_restricted_'. $originator->getId()]],
]));
