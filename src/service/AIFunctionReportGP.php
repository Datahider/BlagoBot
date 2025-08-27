<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\reports\ReportGP;

class AIFunctionReportGP extends AIFunctionReport {
    
    protected $current_session;

    #[\Override]
    public function getResult(array $params): mixed {
        
        if (isset($params['sources'])) {
            if (array_search('all', $params['sources'])) {
                $params['sources'] = [ReportGP::ID_FB, ReportGP::ID_BM, ReportGP::ID_BMO, ReportGP::ID_OMSU, ReportGP::ID_OMSU2];
            }
        } else {
            $params['sources'] = [];
        }
        
        if (!is_array($params['year'])) {
            $params['year'] = [$params['year']];
        }

        $this->sendReport(1, $params);
        
        $result = "Отправлен отчет за период {$params['year'][0]}";
        
        if (!empty($params['sources'])) {
            $result .= "\nВ отчет включена информация по бюджетам: ". implode(', ', $params['sources']);
        }
        
        return $result;
    }
        
}
