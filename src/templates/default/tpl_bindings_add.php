Добавление пользователя <b><?=$user->getFIO();?></b> в качестве <b><?=$type === 'head' ? 'главы' : 'замглавы';?></b>.

<?php

$omsu_roles = [
    'head' => 'глава',
    'vicehead' => 'замглавы'
];

echo "Текущие привязки: ";

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
