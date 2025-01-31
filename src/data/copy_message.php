<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class copy_message extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL',
        'copy_user_id' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'id',
        'INDEX UID' => 'user_id'
    ];
}
