<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\ReportCertificate;
use losthost\DB\DBView;
use losthost\BlagoBot\data\x_omsu;

class ReportCertificateForOmsu extends ReportCertificate {
    protected function getOmsuIds() {
        
        global $b_user;
        
        $omsu = new DBView(<<<FIN
            SELECT id FROM [x_omsu] WHERE head_id = ? OR vicehead_id = ? 
            FIN, [$b_user->id, $b_user->id]);
        
        $result = [];
        while ($omsu->next()) {
            $result[] = $omsu->id;
        }
        
        
        return $result;
    }
    
    protected function checkParamErrors($params): false|array {
        $params['muni'] = $this->getOmsuIds();
        return parent::checkParamErrors($params);
    }
    
    protected function initParams() {
        $this->params = [
            new \losthost\BlagoBot\params\ParamDescriptionYearFull($this)
        ];
    }
    
}
