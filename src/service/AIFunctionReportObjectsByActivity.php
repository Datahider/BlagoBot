<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\service\TableMap;

class AIFunctionReportObjectsByActivity extends AIFunctionReport {

    const DATA_IDS = [
        "Разбивка по бюджетам" => 15, 
        "Опубликовано" => 16, 
        "Законтрактовано" => 17, 
        "Подано заявок на оплату" => 18, 
        "Оплачено" => 19, 
        "Остаток по итогам торгов" => 20, 
        "Остаток по итогам освоения" => 21 
    ];
    
    #[\Override]
    public function getResult(array $params): mixed {

        if (!is_array($params['year'])) {
            $params['year'] = [$params['year']];
        }

        $category_map = new TableMap('x_category', 'id', 'name');
        $activity_ids = $category_map->getReverseMap();
        
        $params['activity'] = array_map(function($value) use ($activity_ids) {
            if (!isset($activity_ids[$value])) {
                throw new \Exception("Не найдено соответствие для значения: $value");
            }
            return $activity_ids[$value];
        }, $params['activity']);
        
        $params['data'] = array_map(function($value) {
            if (!isset(static::DATA_IDS[$value])) {
                throw new \Exception("Не найдено соответствие для значения: $value");
            }
            return static::DATA_IDS[$value];
        }, $params['data']);
        
        
        return $this->sendReport(2, $params);
        
    }
}
