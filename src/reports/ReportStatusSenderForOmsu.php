<?php

namespace losthost\BlagoBot\reports;

use losthost\DB\DBView;
use losthost\BlagoBot\service\ReportSummary;
use losthost\BlagoBot\data\x_omsu;

/**
 * Description of ReportStatusSenderForOmsu
 *
 * @author web
 */
class ReportStatusSenderForOmsu extends ReportStatusSender {
    
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
    
    protected function modParams(&$params) {
        
        global $b_user;
        
        $omsu = new DBView(<<<FIN
            SELECT 
                id AS omsu_id,
                CASE
                    WHEN ? = head_id THEN 86
                    ELSE 87
                END AS recipient
            FROM 
                [x_omsu] 
            WHERE 
                head_id = ? OR vicehead_id = ? 
            FIN, [$b_user->id, $b_user->id, $b_user->id]);
        
        if ($omsu->next()) {
            $params['recipient'] = [$omsu->recipient];
            $params['omsu'] = [$omsu->omsu_id];
        } else {
            $params['recipient'] = 87;
            $params['omsu'] = [];
        }
    }
    
    protected function getCommonStatus($params) {
        $this->modParams($params);
        return parent::getCommonStatus($params);
    }
    
    protected function getDelays($params) {
        $this->modParams($params);
        return parent::getDelays($params);
    }
}
