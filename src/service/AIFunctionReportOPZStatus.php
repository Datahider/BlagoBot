<?php

namespace losthost\BlagoBot\service;

class AIFunctionReportOPZStatus extends AIFunctionReport {

    const SUCCESS = [
        '<' => 'Отправлено сообщение об ОПЗ со статусом "На подписании".',
        '>' => 'Отправлено сообщение об ОПЗ со статусом "Не наступило".'
    ];
    
    #[\Override]
    public function getResult(array $params): mixed {
    
        if (!is_array($params['filter'])) {
            $params['filter'] = [$params['filter']];
        }
        
        $this->sendReport(24, $params);
        
        return self::SUCCESS[$params['filter'][0]];
    }
}
