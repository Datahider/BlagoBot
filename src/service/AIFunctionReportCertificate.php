<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\service\TableMap;

class AIFunctionReportCertificate extends AIFunctionReport {
    
    #[\Override]
    public function getResult(array $params): mixed {
    
        $this->mapParam($params['omsu'], (new TableMap('x_omsu', 'id', 'name'))->getReverseMap());
        return $this->sendReport(5, $params);
    }
}
