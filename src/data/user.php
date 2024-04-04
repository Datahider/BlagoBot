<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class user extends DBObject {
    
    const METADATA = [
        'tg_user' => 'BIGINT NOT NULL',
        'is_admin' => 'TINYINT(1) NOT NULL',
        'is_authorized' => 'TINYINT(1) NOT NULL',
        'auth_code' => 'VARCHAR(10)',
        'PRIMARY KEY' => 'tg_user', 
    ];
}
