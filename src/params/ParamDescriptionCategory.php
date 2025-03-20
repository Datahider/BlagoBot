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
                           FROM [x_object] 
                           WHERE YEAR(open_date_planned) IN (:selected_years)
                          ) 
                ORDER BY name
                FIN;
        $params = Bot::$session->get('data');
        if (empty($params['gpyears'])) {
            $params['gpyears'] = [2024, 2025, 2026, 2027, 2028, 2029];
        }
        $sql = str_replace(":selected_years", implode(',', $params['gpyears']), $sql);
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
