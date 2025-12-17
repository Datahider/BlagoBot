<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DBList;
use losthost\BlagoBot\data\user;

class menu extends DBObject {

    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'parent' => 'BIGINT',
        'sort' => 'BIGINT NOT NULL DEFAULT 0',
        'is_active' => 'TINYINT(1) NOT NULL DEFAULT 1',
        'title' => 'VARCHAR(64)',
        'description' => 'VARCHAR(1024)',
        'type' => 'ENUM("submenu", "report", "link") NOT NULL DEFAULT "submenu"',
        'subtype_id' => 'BIGINT',
        'handler_class' => 'VARCHAR(256)',
        'handler_param' => 'VARCHAR(256)',
        'accessed_by' => 'VARCHAR(4) NOT NULL DEFAULT "a"',
        'PRIMARY KEY' => 'id'
    ];
    
    public function getChildren(string $access_level) {
        
        $accessed_by = '%'. substr($access_level, 0, 1). '%';
        $children = new DBList(menu::class, 'is_active = 1 AND parent = ? AND accessed_by LIKE ? ORDER BY sort', [$this->id, $accessed_by]);
        return $children->asArray();
    }
    
    public function getTitle() {
        return $this->title;
    }
}
