<?php
use losthost\BlagoBot\reports\AbstractReport;

if ($result->ok) {
    switch ($result->result_type) {
        case AbstractReport::RESULT_TYPE_SHOW:
            echo "Обработка завершена. Результаты обработки:\n\n";
            
            if (!empty($result->summary->getParams()['stat'])) {
                foreach($result->summary->getParams()['stat'] as $key=>$value) {
                    echo "$key: $value\n";
                }
                echo "\n";
            }
            
            foreach ($result->data as $line) {
                foreach ($result->columns as $key => $column) {
                    echo "$column: $line[$key]\n";
                }
                echo "\n";
            }
            break;
        case AbstractReport::RESULT_TYPE_CUSTOM:
            foreach ($result->data as $line) {
                foreach ($line as $value) {
                    echo $value;
                    echo "\n\n";
                }
            }
            break;
        case AbstractReport::RESULT_TYPE_XLSX:
            echo "Результат отчета будет отправлен в виде файла";
            break;
        case AbstractReport::RESULT_TYPE_NONE:
            break; // Никакой вывод не требуется
        default:
            if (is_string($result->result_type)) { 
                // предполагаем, что result_type -- это имя класса, который знает что и как выводить
                echo "Результат(ы) отчета:";
            } else {
                echo "Обработчик вернул неизвестный тип результата. Обратитесь к разработчику.";
            }
    }
} else {
    echo "При создании отчета возникли ошибки:\n\n";
    echo implode("\n\n", $result->errors);
}