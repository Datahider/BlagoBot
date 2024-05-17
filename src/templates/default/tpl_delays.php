<?php
    $last_digit = substr($data['total_objects'], -1);
    switch ($last_digit) {
    case '1':
        $objects_end = '';
        break;
    case '2':
    case '3':
    case '4':
        $objects_end = 'а';
        break;
    default:
        $objects_end = 'ов';
    }

?><b>Уважаемый <?=$data['user_name'];?> <?=$data['user_fathers_name'];?>!</b>

На городской округ <?=$data['omsu_name'];?> в <?=$data['current_year'];?> году в рамках госпрограммы «Формирование современной комфортной городской среды» предусмотрено финансирование в размере <b><?= number_format($data['total_limit'], 0, '', ' ');?> тыс. руб.</b> на <?=$data['total_objects'];?> объект<?=$objects_end;?>.

На <b><?=date_create($data['current_date'])->format('d.m.Y');?></b> городской округ <?=$data['omsu_name'];?> находится в красной зоне по просрочке плановых сроков по:
<?php

use losthost\BlagoBot\reports\ReportStatusSender;

for ($i = 1; $i<=7; $i++) {
    if (count($data[$i])) {
        echo "\n<b>". ReportStatusSender::DELAY_TYPES[$i]. ":</b>\n";
        foreach ($data[$i] as $object_data) {
            $date_planned = $object_data['date_planned']->format('d.m.Y');
            echo "• $object_data[object_name] ($date_planned)\n";
        }
    }
}

?>

Просим доложить по данному вопросу и в максимально короткие сроки обеспечить публикацию по указанным объектам и заключение контрактов!