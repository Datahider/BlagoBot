<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_category extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'name' => 'VARCHAR(64) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX NAME' => 'name'
    ];
    
    public function getTitle() {
        return $this->name;
    }
}
