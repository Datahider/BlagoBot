<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DBList;
use losthost\BlagoBot\data\report_param_value;
use losthost\BlagoBot\data\report;

class report_param extends DBObject {

    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'report' => 'BIGINT',
        'value_set' => 'VARCHAR(128)',
        'sort' => 'BIGINT NOT NULL DEFAULT 0',
        'is_active' => 'TINYINT(1) NOT NULL DEFAULT 1',
        'name' => 'VARCHAR(32)',
        'title' => 'VARCHAR(64)',
        'description' => 'VARCHAR(1024)',
        'is_mandatory' => 'TINYINT(1) NOT NULL',
        'is_multiple_choise' => 'TINYINT(1) NOT NULL',
        'choise_type' => 'ENUM("buttons", "aphabet") NOT NULL',
        'max_buttons' => 'TINYINT(4) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX REPORT_NAME' => ['report', 'name']
    ];
    
    public function valuesArray() {
        $value_set = $this->value_set;
        switch ($value_set) {
            case 'omsu': 
                $values = new DBList(x_omsu::class, '1 ORDER BY name', []);
                break;
            case 'activity':
                $values = new DBList(x_category::class, <<<FIN
                        id IN (
                            SELECT DISTINCT
                                    category.id
                            FROM 
                                    [x_category] AS category
                                    LEFT JOIN [x_object] AS object ON object.x_category_id = category.id
                                    LEFT JOIN [x_year_data] AS year_data ON year_data.x_object_id = object.id AND year_data.year = ?
                            WHERE year_data.id IS NOT NULL
                        ) ORDER BY name
                        FIN, [2024]); // TODO - Заменить год на текущий
                break;
            default:
                if (is_a($this->value_set, DBObject::class, true)) {
                    $class = $this->value_set;
                    $values = new DBList($class, "1 ORDER BY sort", []);
                } else {
                    $values = new DBList(report_param_value::class, "value_set = ? AND is_active = 1 ORDER BY sort, title", $this->value_set);
                }
        }
        return $values->asArray();
    }
    
    public function getName() {
        return $this->name;
    }

    public function getTitle() {
        return $this->title;
    }
    
    public function isMandatory() {
        return $this->is_mandatory;
    }
    
    public function isMultipleChoice() {
        return $this->is_multiple_choise;
    }

    public function getValueSet() {
        return $this->value_set;
    }
    
    public function getReportClass() {
        $report = new report(['id' => $this->report]);
        return $report->handler_class;
    }
}
