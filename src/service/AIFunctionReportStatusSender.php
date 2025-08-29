<?php

namespace losthost\BlagoBot\service;

class AIFunctionReportStatusSender extends AIFunctionReport {
    
    const MSG_TYPES = [
        'Общий статус реализации программы' => 88, 
        'Риски по срокам' => 89
    ];
    
    const RECIPIENT = [
        'Глава' => 86, 
        'Замглавы' => 87
    ];
    
    #[\Override]
    public function getResult(array $params): mixed {
    
        $omsu_table = new TableMap('x_omsu', 'id', 'name');
        $this->mapParam($params['omsu'], $omsu_table->getReverseMap());
        $this->mapParam($params['recipient'], self::RECIPIENT);
        $params['recipient'] = [$params['recipient']];
        $this->mapParam($params['msgtype'], self::MSG_TYPES);
        $params['msgtype'] = [$params['msgtype']];
        
        $this->sendReport(6, $params);
        
        return "Сообщения отправлены.";
    }
}
