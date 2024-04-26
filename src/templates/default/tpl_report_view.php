<?php
use losthost\BlagoBot\reports\AbstractReport;

if ($result->ok) {
    switch ($result->result_type) {
        case AbstractReport::RESULT_TYPE_SHOW:
            echo "Обработка завершена. Результаты обработки:\n\n";
            foreach ($result->data as $line) {
                foreach ($result->columns as $key => $column) {
                    echo "$column: <b>$line[$key]</b>\n";
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
        default:
            echo "Обработчик вернул неизвестный тип результата. Обратитесь к разработчику.";
    }
} else {
    echo "При создании отчета возникли ошибки:\n\n";
    echo implode("\n\n", $result->errors);
}