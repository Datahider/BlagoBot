<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class user_dept_binding extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL',
        'dept_id' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX USER_DEPT' => ['user_id', 'dept_id'],
    ];
}
