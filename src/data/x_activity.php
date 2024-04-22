<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_activity extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'gasu_code' => 'VARCHAR(10) NOT NULL',
        'name' => 'VARCHAR(32) NOT NULL',
        'PRIMARY KEY' => 'id',
    ];
}
