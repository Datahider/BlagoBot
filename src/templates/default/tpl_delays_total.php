<?php

    use function losthost\BlagoBot\isWoman;

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

?><b><?=isWoman($data['user_name']) ? "Уважаемая" : "Уважаемый";?> <?=$data['user_name'];?> <?=$data['user_fathers_name'];?>!</b>

В <?=$data['current_year'];?> году в рамках госпрограммы «Формирование современной комфортной городской среды» предусмотрено финансирование в размере <b><?= number_format($data['total_limit'], 0, '', ' ');?> тыс. руб.</b> на <?=$data['total_objects'];?> объект<?=$objects_end;?>.

На <b><?=date_create($data['current_date'])->format('d.m.Y');?></b> находится в красной зоне по просрочке плановых сроков по:
<?php

use losthost\BlagoBot\reports\ReportStatusSender;

for ($i = 1; $i<=7; $i++) {
    if (count($data[$i])) {
        echo '<!-- SPLIT -->';
        $more_data_array = [];
        foreach ($data['more_data'. $i] as $key => $value) {
            $more_data_array[] = "$key $value";
        }
        $more_data = implode(', ', $more_data_array);
        echo "<b>". ReportStatusSender::DELAY_TYPES[$i]. ":($more_data)</b>\n";
        $current_length = 0;
        foreach ($data[$i] as $object_data) {
            $date_planned = $object_data['date_planned']->format('d.m.Y');
            $text = "• $object_data[object_name] ($date_planned)\n";
            $current_length += strlen($text);
            
            if ($current_length > 3072) {
                echo '<!-- SPLIT -->';
                $current_length = 0;
            }
            echo $text;
        }
    }
}

?>
