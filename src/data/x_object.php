<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_object extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'uin' => 'VARCHAR(25) NOT NULL',
        'omsu_id' => 'BIGINT(20) NOT NULL', 
        'status' => 'VARCHAR(64) NOT NULL',
        'status2' => 'VARCHAR(64) NOT NULL',
        'work_type' => 'VARCHAR(64)',
        'full_name' => 'VARCHAR(2048) NOT NULL',
        'short_name' => 'VARCHAR(256) NOT NULL',
        'name' => 'VARCHAR(256) NOT NULL',
        'category_id' => 'BIGINT(20) NOT NULL',
        'gasu_code' => 'VARCHAR(10) NOT NULL',
        'gasu_date' => 'DATETIME',
        'report_status1' => 'VARCHAR(64)',
        'report_status2' => 'VARCHAR(64)',
        'ready_percent' => 'DECIMAL',
        'object_char' => 'VARCHAR(64)',
        'type' => 'VARCHAR(64)',
        'period' => 'VARCHAR(64)',
        'open_date_planned' => 'DATETIME',
        'object_count' => 'INT(11)',
        'rg_date' => 'DATETIME',
        'nmck_date' => 'DATETIME',
        'nmck_opz_date' => 'DATETIME',
        'nmck_numsign' => 'VARCHAR(20)',
        'ikz' => 'VARCHAR(60)',
        'contract_winner' => 'VARCHAR(128)',
        'contract_inn' => 'VARCHAR(16)',
        'contract_number' => 'VARCHAR(32)',
        'contract_date' => 'DATETIME',
        'href' => 'VARCHAR(512)',
        'PRIMARY KEY' => 'id'
    ];
}
