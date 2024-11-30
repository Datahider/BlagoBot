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
        'accessed_by' => 'VARCHAR(4) NOT NULL DEFAULT "a"',
        'PRIMARY KEY' => 'id'
    ];
    
    public function paramsArray() {

        $param_handler_class = $this->handler_class;
        $ph = new $param_handler_class;
        
        if (is_null($ph->getParams())) {
            $params = new DBList(report_param::class, "report = ? AND is_active = 1 ORDER BY sort, title", $this->id);
            return $params->asArray();
        }
        
        return $ph->getParams();
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
    
    public function setDefaultParamValues() {
        $report_handler = $this->handler_class;
        
        $ph = new $report_handler();
        if ($ph->getParams() === null) {
            return false;
        }
        
        $ph->setDefaultParamValues();
        return true;
    }
    
    public function getTitle() {
        return $this->title;
    }
}
