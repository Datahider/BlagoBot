<b><?=$report->description;?>.</b>

<?php

use losthost\telle\Bot;

// Если параметр один и он не имеет множественного выбора - надо сразу показать кнопки и сформировать отчет при выборе кнопки
if (count($report_params) == 0) {
    echo 'Для формирования отчета нажмите кнопку Сформировать.';
} elseif (count($report_params) == 1 && !$report_params[0]->is_multiple_choise) {
    echo 'Для формирования отчета выберите '. $report_params[0]->title;
} elseif (count($report_params) == 1) {
    echo 'Для формирования отчета выберите необходимые значения для '. $report_params[0]->title. ' и нажмите кнопку Сформировать.';
} else {
    echo "Для формирования отчета установите значения параметров и нажмите кнопку Сформировать.\n\n";
    echo "<u>Параметры:</u>\n\n";
    
    foreach ($report_params as $param) {
        $title = $param->title;
        if ($param->is_mandatory) {
            $title = "<b>$title</b>";
        }
        $value = $selected_params->paramTitlesAsString($param->name);
        echo "$title: <i>$value</i>\n\n";
    }

}


