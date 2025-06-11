<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_prev extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'year' => 'INT(11) NOT NULL',
        'omsu_name' => 'VARCHAR(64) NOT NULL',
        'object_name' => 'VARCHAR(256) NOT NULL',
        'category2_name' => 'VARCHAR(64) NOT NULL',
        'object_count' => 'INT(11) NOT NULL',
        'payment_total' => 'DECIMAL',
        'contract_inn' => 'VARCHAR(12)',
        'contract_winner' => 'VARCHAR(128)',
        'PRIMARY KEY' => 'id'
    ];
    
    /**
     * Получает массив лет в которые реализовывался этот объект в прошлых годах
     * 
     * @param string $omsu_name      - название ОМСУ
     * @param string $object_name    - название объекта
     * @param string $category2_name - название категории 2
     * @return array - массив, содежащий цифровые значения лет реализации
     */
    public static function getYears(string $omsu_name, string $object_name, string $category2_name) : string {
        
    }
}
