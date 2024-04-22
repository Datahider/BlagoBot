<?php

$headers = [
    'info' => 'ℹ️ <b>Информационное сообщение</b>',
    'error' => '🛑 <b>Ошибка</b>'
];

$header = isset($headers[$type]) ? $headers[$type] : headers['error'];

echo "$header\n\n$text";

