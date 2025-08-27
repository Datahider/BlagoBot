<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class ai_context extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'date_added' => 'DATETIME NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'role' => 'ENUM("user", "assistant", "system")',
        'text' => 'MEDIUMTEXT',
        'data' => 'MEDIUMTEXT',
        'PRIMARY KEY' => 'id',
        'INDEX idx_1' => ['user_id', 'date_added']
    ];
}
