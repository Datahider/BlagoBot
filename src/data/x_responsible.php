<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DBValue;

class x_responsible extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL DEFAULT 0',
        'sort' => 'BIGINT(20) NOT NULL DEFAULT 0',
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
        $responsible_id = new DBValue('SELECT responsible.id AS value FROM [x_responsible] AS responsible INNER JOIN [user] AS user ON user.id = responsible.user_id  WHERE surname = ? AND name LIKE ? AND fathers_name LIKE ?', $parsed);
        
        return new x_responsible(['id' => $responsible_id->value]);
    }
    
    public function getTitle() {
        $responsible = new DBValue('SELECT user.surname AS surname, user.name AS name, user.fathers_name AS fathers_name FROM [x_responsible] AS responsible INNER JOIN [user] AS user ON user.id = responsible.user_id  WHERE responsible.id = ?', [$this->id]);
        return sprintf('%s %s.%s.', $responsible->surname, mb_substr($responsible->name, 0, 1), mb_substr($responsible->fathers_name, 0, 1));
    }
    
    public function getValue() {
        return $this->getTitle();
    }
    
    public function getId() {
        return $this->id;
    }
}


