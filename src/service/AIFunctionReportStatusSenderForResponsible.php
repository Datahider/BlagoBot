<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\BlagoBot\service;

class AIFunctionReportStatusSenderForResponsible extends AIFunctionReport {
    
    const MSG_TYPES = [
        'Общий статус реализации программы' => 88, 
        'Риски по срокам' => 89
    ];
    
    #[\Override]
    public function getResult(array $params): mixed {

        $responsible_table = new TableMap('x_responsible_view', 'id', 'fio');
        $this->mapParam($params['responsible'], $responsible_table->getReverseMap());
        $this->mapParam($params['msgtype'], self::MSG_TYPES);
        $params['msgtype'] = [$params['msgtype']];
        $params['selfcopy'] = [$params['selfcopy']];
        
        if (!$params['period']) {
            return $this->sendReport(16, $params);
        } else {
            return $this->sendReport(20, $params);
        }
        
    }
}
