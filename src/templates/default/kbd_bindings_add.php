<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

$keyboard = [];

$prop = $type. '_id';
foreach ($omsus as $omsu) {
    if ($omsu->$prop === $user->id) {
        continue;
    }
    if ($omsu->$prop !== null) {
        continue;
    }

    $keyboard[] = [['text' => $omsu->name, 'callback_data' => "edit_bind{$type}_{$user->id}_$omsu->id"]];
}

$keyboard[] = [['text' => '🔙 Назад', 'callback_data' => "edit_bindings_$user->id"]];

echo serialize(new InlineKeyboardMarkup($keyboard));