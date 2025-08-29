<?php

namespace losthost\BlagoBot\service;

use losthost\DB\DBView;
/**
 * Содержит функции получения и преобразования данных (например id <-> name)
 * в соответствии с заданной таблицей БД
 *
 * @author web
 */
class TableMap {
    
    const BRACKETS_NONE = 0;
    const BRACKETS_SQUARE = 1;
    
    protected array $map;
    protected array $reverse_map;
    
    public function __construct(string $table_name, string $key_field_name, string $value_field_name) {
        
        $view = new DBView($this->getSQL($table_name, $key_field_name, $value_field_name));
        
        $this->map = [];
        $this->reverse_map = [];
        
        while ($view->next()) {
            $this->map[$view->$key_field_name] = $view->$value_field_name;
            $this->reverse_map[$view->$value_field_name] = $view->$key_field_name;
        }
    }

    public function valueByKey($key) {
        return $this->map[$key];
    }
    
    public function keyByValue($value) {
        return $this->reverse_map[$value];
    }
    
    public function implodeValues($brackets=self::BRACKETS_NONE) {
        
        $result = '"'. implode('", "', $this->map). '"';
        
        if ($brackets == self::BRACKETS_SQUARE) {
            $result = '['. $result. ']';
        }
        
        return $result;
    }
    
    public function values() {
        return array_values($this->map);
    }
    
    public function keys() {
        return array_values($this->reverse_map);
    }
    
    public function getMap() {
        return $this->map;
    }
    
    public function getReverseMap() {
        return $this->reverse_map;
    }
    
    protected function getSQL($table_name, $key_field_name, $value_field_name) {
        return <<<FIN
            SELECT
                $key_field_name,
                $value_field_name
            FROM 
                [$table_name]
            FIN;
    }
    
}
