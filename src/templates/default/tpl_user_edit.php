<u>Редактирование пользователя  /<?=$user->id;?></u>

<b><?=$user->getTelegramName();?></b>
    
<?php

$roles = [
    'user' => 'Пользователь МО',
    'admin' => 'Администратор',
    'restricted' => 'Пользователь ОМСУ',
    'operator' => 'Оператор',
    'unknown' => 'Чужой'
];

$omsu_roles = [
    'head' => 'глава',
    'vicehead' => 'замглавы'
];

if ($user->surname === null && $user->name === null && $user->fathers_name === null) {
    echo "ФИО: <b>--НЕ ЗАДАНО--</b>\n";
} else {
    echo "ФИО: <b>$user->surname $user->name $user->fathers_name</b>\n";
}

echo "Роль: <b>{$roles[$user->access_level]}</b>\n";

echo "Привязки: ";

if (count($bindings) === 0) {
    echo "-\n";
} else {
    echo '<b>';
    $coma = '';
    foreach ($bindings as $binding) {
        echo "$coma$binding[1] ({$omsu_roles[$binding[2]]})";
        $coma = ', ';
    }
    echo "</b>\n";
}

if (empty($end)) {
    echo "\n<i>Для изменения ФИО пользователя отправьте новое значение.</i>";
}
