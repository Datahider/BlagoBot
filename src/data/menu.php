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
        'button_text' => 'VARCHAR(64)',
        'description' => 'VARCHAR(1024)',
        'handler_class' => 'VARCHAR(256)',
        'PRIMARY KEY' => 'id'
    ];
    
    public function getChildren() {
        
        $children = new DBList(menu::class, 'is_active = 1 AND parent = ? ORDER BY sort', [$this->id]);
        return $children->asArray();
    }
}
