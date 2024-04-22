<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_contract extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'x_contragent_id' => 'BIGINT(20) NOT NULL',
        'x_object_id' => 'BIGINT(20) NOT NULL',
        'number' => 'VARCHAR(16) NOT NULL',
        'date' => 'DATETIME NOT NULL',
        'PRIMARY KEY' => 'id'
    ];
}
