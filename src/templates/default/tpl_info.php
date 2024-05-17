<?php

$headers = [
    'info' => 'ℹ️ <b>Информационное сообщение</b>',
    'error' => '🛑 <b>Ошибка</b>',
    'custom' => ''
];

$header = isset($headers[$type]) ? $headers[$type] : headers['error'];

if ($type == 'custom') {
    echo $text;
} else {
    echo "$header\n\n$text";
}

