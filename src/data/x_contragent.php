<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_contragent extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'inn' => 'VARCHAR(12) NOT NULL',
        'name' => 'VARCHAR(128) NOT NULL',
        'PRIMARY KEY' => 'id', 
        'UNIQUE INDEX INN' => 'inn'
    ];
}
