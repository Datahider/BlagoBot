<?php

namespace losthost\BlagoBot\params;

use losthost\DB\DBView;
use losthost\telle\Bot;

class ParamDescriptionCategory extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [];
        $sql = <<<FIN
                SELECT id, name 
                FROM [x_category] 
                WHERE
                    id IN (SELECT DISTINCT x_category_id
                           FROM [x_object] AS object 
                           LEFT JOIN [x_year_data] AS fb ON object.id = fb.x_object_id AND fb.type = "Лимит ФБ" AND fb.year IN (:year)
                           LEFT JOIN [x_year_data] AS bm ON object.id = bm.x_object_id AND bm.type = "Лимит БМ" AND bm.year IN (:year)
                           LEFT JOIN [x_year_data] AS bmo ON object.id = bmo.x_object_id AND bmo.type = "Лимит БМО" AND bmo.year IN (:year)
                           LEFT JOIN [x_year_data] AS omsu ON object.id = omsu.x_object_id AND omsu.type = "Лимит ОМСУ" AND omsu.year IN (:year)

                           WHERE YEAR(open_date_planned) IN (:selected_years)
                           GROUP BY x_category_id
                           HAVING
                              SUM(IFNULL(fb.value, 0)) + SUM(IFNULL(bm.value, 0)) + SUM(IFNULL(bmo.value, 0)) + SUM(IFNULL(omsu.value, 0)) > 0
                          ) 
                ORDER BY name
                FIN;
        $params = Bot::$session->get('data');
        if (empty($params['gpyears'])) {
            $params['gpyears'] = [2024, 2025, 2026, 2027, 2028, 2029];
        }
        if (empty($params['year'])) {
            $params['year'] = [2024, 2025, 2026, 2027, 2028, 2029];
        }
        
        $sql = str_replace(":selected_years", implode(',', $params['gpyears']), $sql);
        $sql = str_replace(":year", implode(',', $params['year']), $sql);
        $category = new DBView($sql);
        
        while ($category->next()) {
            $this->value_set[] = new ParamValue($category->name, $category->id);
        }
        
        $this->defaults = [];
    }

    public function getName(): string {
        return 'activity';
    }

    public function getPrompt(): string {
        return 'Выберите мероприятия';
    }

    public function getTitle(): string {
        return 'Мероприятия';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
