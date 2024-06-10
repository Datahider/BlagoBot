<?php

    use function losthost\BlagoBot\isWoman;
    use function losthost\BlagoBot\objectCommonStatus;
    use function \losthost\BlagoBot\procedurePlanFact;
    use function \losthost\BlagoBot\objectHasDelays;
    
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
    $date_formatted = date_create($data['current_date'])->format('d.m.Y');
    
?><b><?=isWoman($data['user_name']) ? "Уважаемая" : "Уважаемый";?> <?=$data['user_name'];?> <?=$data['user_fathers_name'];?>!</b>

На городской округ <?=$data['omsu_name'];?> в <?=$data['current_year'];?> году в рамках госпрограммы «Формирование современной комфортной городской среды» предусмотрено финансирование в размере <b><?= number_format($data['total_limit'], 0, '', ' ');?> тыс. руб.</b> на <?=$data['total_objects'];?> объект<?=$objects_end;?>.

Статус реализации по каждому объекту на <b><?=$date_formatted;?></b> в последующих сообщениях:
<!-- SPLIT -->
<?php

use losthost\BlagoBot\reports\ReportStatusSender;

$total_delays = 0;

foreach ($data['object_data'] as $key => $object_data) {
    echo $key+1, ". <b>$object_data[object_name]</b>\n";
    $total_limit = number_format($object_data['total_limit'], 0, '', ' ');
    echo "Финансирование: $total_limit тыс. руб.\n";
    echo objectCommonStatus($object_data). "\n\n";

    if (objectHasDelays($object_data)) {
        $total_delays++;
    }
    if (!$object_data['open_date_fact']) {
        echo procedurePlanFact('Заход в МОГЭ', $object_data['moge_in_plan'], $object_data['moge_in_fact']). "\n";
        echo procedurePlanFact('Получение заключения МОГЭ', $object_data['moge_out_plan'], $object_data['moge_out_fact']). "\n";

        if ($object_data['purchase_level'] == 1) {
            echo procedurePlanFact('Заход на согласование РГ ККП', $object_data['rgmin_in_plan'], $object_data['rgmin_in_fact']). "\n";
        } else {
            echo procedurePlanFact('Заход на согласование в министерство', $object_data['rgmin_in_plan'], $object_data['rgmin_in_plan']). "\n";
        }

        echo procedurePlanFact('Публикация СМР', $object_data['psmr_plan'], $object_data['psmr_fact']). "\n";
        echo procedurePlanFact('Контрактация СМР', $object_data['ksmr_plan'], $object_data['ksmr_fact']). "\n";
        echo procedurePlanFact('Открытие объекта', $object_data['open_date_planned'], $object_data['open_date_fact']). "\n";
        echo '<!-- SPLIT -->';
    }
}

if ($total_delays) :
    $total_in_time = $data['total_objects'] - $total_delays;
    echo <<<FIN
        Вывод: на $date_formatted «<b>$total_delays</b>» объектов из «<b>$data[total_objects]</b>» отстают от плана реализации и находятся в риске по срыву срока открытия.
        «<b>$total_in_time</b>» – находятся в графике.
        FIN;
else :
    echo <<<FIN
        Вывод: на $date_formatted все объекты ($data[total_objects]) находятся в графике.
        FIN;
endif;
