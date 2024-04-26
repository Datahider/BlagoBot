<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_year_data extends DBObject {
    
    const TYPE_SMR = "СМР";
    const TYPE_PIR = "ПИР";
    const TYPE_LIMIT_FB = "Лимит ФБ";
    const TYPE_LIMIT_BM = "Лимит БМ";
    const TYPE_LIMIT_BMO = "Лимит БМО";
    const TYPE_LIMIT_OMSU = "Лимит ОМСУ";
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'year' => 'INT(11) NOT NULL',
        'x_object_id' => 'BIGINT(20) NOT NULL',
        'type' => 'ENUM("СМР","ПИР","Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ") NOT NULL',
        'value' => 'DECIMAL',
        'note' => 'VARCHAR(1024)',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX YOT' => ['year', 'x_object_id', 'type']
    ];
    
}
