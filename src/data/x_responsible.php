<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DBValue;

class x_responsible extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'surname' => 'VARCHAR(50)',
        'name' => 'VARCHAR(50)',
        'fathers_name' => 'VARCHAR(50)',
        'PRIMARY KEY' => 'id',
    ];
    
    static public function getByFio($param) {
        
        $parsed = preg_split("/[\s.]+/u", $param);
        if (count($parsed) != 4) {
            throw new \Exception("Не верный формат ФИО");
        }
        
        unset($parsed[3]);
        $parsed[1] .= '%';
        $parsed[2] .= '%';
        $responsible_id = new DBValue('SELECT id AS value FROM [x_responsible] WHERE surname = ? AND name LIKE ? AND fathers_name LIKE ?', $parsed);
        
        return new x_responsible(['id' => $responsible_id->value]);
    }
}


