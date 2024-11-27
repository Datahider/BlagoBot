<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class report_param_value extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'value_set' => 'VARCHAR(32)',
        'sort' => 'BIGINT NOT NULL DEFAULT 0',
        'is_active' => 'TINYINT(1) NOT NULL DEFAULT 1',
        'title' => 'VARCHAR(64)',
        'value' => 'VARCHAR(256)',
        'is_default' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX TITLE_SET' => ['value_set', 'title']
    ];
    
    public function getTitle() {
        return $this->title;
    }
}
