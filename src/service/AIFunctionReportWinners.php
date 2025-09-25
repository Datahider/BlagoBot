<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\service\TableMap;
use losthost\BlagoBot\params\ParamDescriptionCategory2All;

class AIFunctionReportWinners extends AIFunctionReport {
    
    #[\Override]
    public function getResult(array $params): mixed {

        $winners_map = new TableMap('x_contragent', 'id', 'name');
        $omsu_map = new TableMap('x_omsu', 'id', 'name');
        
        if ($params['winners'][0] == 'Все') {
            $params['winners'] = array_values($winners_map->getReverseMap());
        } else {
            $this->mapParam($params['winners'], $winners_map->getReverseMap());
        }
        
        if ($params['omsu'][0] == 'Все') {
            $params['omsu'] = array_values($omsu_map->getReverseMap());
        } else {
            $this->mapParam($params['omsu'], $omsu_map->getReverseMap());
        }

        $cat2_param = new ParamDescriptionCategory2All($this);
        if ($params['cat2'][0] == 'Все') {
            $cat2 = [];
            foreach ($cat2_param->getValueSet() as $value) {
                $cat2[] = $value->getValue();
            }
            $params['cat2'] = $cat2;
        } else {
            $this->mapParam($params['cat2'], $cat2_param->getReverseMap());
        }
        
        return $this->sendReport(21, $params);
        
    }
}
