Результат отчета:

<?php

if ($result->ok) {
    $columns = array_values($result->columns);
    foreach ($result->data as $line) {
        echo "$line[0]. $line[1]: $line[2]\n";
    }
} else {
    echo "При создании отчета возникли ошибки:\n\n";
    echo implode("\n\n", $result->errors);
}