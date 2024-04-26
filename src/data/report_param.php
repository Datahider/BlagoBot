<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DBList;
use losthost\BlagoBot\data\report_param_value;

class report_param extends DBObject {

    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'report' => 'BIGINT',
        'value_set' => 'VARCHAR(32)',
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
            default:
                $values = new DBList(report_param_value::class, "value_set = ? AND is_active = 1 ORDER BY sort, title", $this->value_set);
        }
        return $values->asArray();
    }
}
