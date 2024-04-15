<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DBList;

class menu extends DBObject {

    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'parent' => 'BIGINT',
        'sort' => 'BIGINT NOT NULL DEFAULT 0',
        'is_active' => 'TINYINT(1) NOT NULL DEFAULT 1',
        'title' => 'VARCHAR(64)',
        'description' => 'VARCHAR(1024)',
        'type' => 'ENUM("submenu", "report") NOT NULL DEFAULT "submenu"',
        'subtype_id' => 'BIGINT',
        'handler_class' => 'VARCHAR(256)',
        'handler_param' => 'VARCHAR(256)',
        'PRIMARY KEY' => 'id'
    ];
    
    public function getChildren() {
        
        $children = new DBList(menu::class, 'is_active = 1 AND parent = ? ORDER BY sort', [$this->id]);
        return $children->asArray();
    }
}
