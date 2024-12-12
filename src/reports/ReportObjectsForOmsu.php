<?php

namespace losthost\BlagoBot\reports;

use losthost\DB\DBView;
use losthost\BlagoBot\service\ReportSummary;
use losthost\BlagoBot\data\x_omsu;

/**
 * Отчет для пользователя ОМСУ
 *
 * @author web
 */
class ReportObjectsForOmsu extends ReportObjectsByOmsu {
    
    protected function initParams() {
        $this->params = [
            new \losthost\BlagoBot\params\ParamDescriptionDataIncluded($this)
        ];
    }

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
    
    protected function queryReportData($params) {
        $params['omsu'] = $this->getOmsuIds();
        return parent::queryReportData($params);
    }
    
    protected function reportSummary($params): ReportSummary {
        $omsus = [];
        foreach ($this->getOmsuIds() as $id) {
            $omsu = new x_omsu(['id' => $id]);
            $omsus[] = $omsu->name;
        }
        return new ReportSummary(
                'Статус реализации мероприятий по ГП "Формирование современной комфортной городской среды" в 2024 году', 
                date_create_immutable(), 
                [
                    ['title' => 'ОМСУ', 'value' => implode(', ', $omsus)]
                ]
                );
                
    }
    
}
