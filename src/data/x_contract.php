<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_contract extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'x_contragent_id' => 'BIGINT(20)',
        'x_object_id' => 'BIGINT(20) NOT NULL',
        'status' => 'ENUM("ГП","РГ","Закупки","Контракт","Прочее") NOT NULL',
        'status2' => 'VARCHAR(32) NOT NULL',
        'number' => 'VARCHAR(32)',
        'date' => 'DATETIME',
        'has_pir' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'has_smr' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'nmck_opz_date' => 'DATETIME',
        'nmck_purchase_number' => 'VARCHAR(30)',
        'PRIMARY KEY' => 'id'
    ];
}
