<?php

namespace losthost\BlagoBot\reports;

use losthost\telle\Bot;
use losthost\BlagoBot\reports\AbstractReport;
use losthost\BlagoBot\reports\ReportParams;
use losthost\BlagoBot\service\ReportSummary;

class DummyReport extends AbstractReport {
    
    protected function checkParamErrors($params): false|array {
        if (count($params) > 0) {
            return false;
        }
        return [
            'Не заданы значения параметров.'
        ];
    }

    protected function reportColumns(): array {
        return [
            'Номер п/п',
            'Имя параметра',
            'Значение параметра'
        ];
    }

    protected function reportData($params): array {
        $result = [];
        $count = 0;
        $report_params = new ReportParams($params);
        
        foreach ($params as $key => $param) {
            foreach ($report_params->paramValuesAsArray($key) as $value) {
                $count++;
                $result[] = $this->newLine($count, $report_params->paramTitle($key), $value);
            }
        }
        return $result;
    }
    
    protected function newLine($count, $param_name, $param_value) {
        return [$count, $param_name, $param_value];
    }

    protected function resultType(): int {
        return static::RESULT_TYPE_SHOW;
    }

    protected function reportSummary($params): ReportSummary {
    
        return new ReportSummary(
                'Отчет-пустышка', 
                date_create_immutable(), 
                []);
    }
}
