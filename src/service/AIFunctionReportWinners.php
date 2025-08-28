<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\service\TableMap;

class AIFunctionReportWinners extends AIFunctionReport {
    
    #[\Override]
    public function getResult(array $params): mixed {

        $winners_map = new TableMap('x_contragent', 'id', 'name');
        
        $params['winners'] = array_map(function($value) use ($winners_map) {
            $key = $winners_map->keyByValue($value);
            if (!isset($key)) {
                throw new Exception("Не найдено соответствие для значения: $value");
            }
            return $key;
        }, $params['winners']);
        
        $this->sendReport(21, $params);
        
        $result = "Запрошенный отчет отправлен.";

        return $result;
    }
}
