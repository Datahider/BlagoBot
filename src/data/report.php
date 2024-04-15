<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\BlagoBot\data\report_param;
use losthost\DB\DBList;

class report extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'sort' => 'BIGINT NOT NULL DEFAULT 0',
        'is_active' => 'TINYINT(1) NOT NULL DEFAULT 1',
        'title' => 'VARCHAR(64)',
        'description' => 'VARCHAR(1024)',
        'handler_class' => 'VARCHAR(256)',
        'handler_param' => 'VARCHAR(256)',
        'PRIMARY KEY' => 'id'
    ];
    
    public function paramsArray() {
        
        $params = new DBList(report_param::class, "report = ? AND is_active = 1 ORDER BY sort, title", $this->id);
        return $params->asArray();
    }
    
    public function hasNoParams() {
        return count($this->paramsArray()) == 0; 
    }
    
    public function hasOneParam() {
        return count($this->paramsArray()) == 1; 
    }
    
    public function isFastSelect() {
        $params = $this->paramsArray();
        return count($params) == 1 && !$params[0]->is_multiple_choise; 
    }
}
