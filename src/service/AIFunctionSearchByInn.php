<?php

namespace losthost\BlagoBot\service;

class AIFunctionSearchByInn extends AIFunction {
    
    const SP_COMMON_DATA = "Общая информация о компании по ИНН %s. Статус, руководитель, выручка и прибыль за предыдущий год";
    const SP_CONTRACT_STAT = "Общая статистика по контрактам компании с ИНН %s, включая общий портфель, количество контрактов и госзакупки";
    const SP_DINAMICS = "Динамика контрактной деятельности компании c ИНН %s в разбивке по годам";
    const SP_RECORDS = "Реестровые записи по госконтрактам компании с ИНН %s за предыдущие годы начиная с 2021";
    const SP_SPEC = "Информация о специализации компании с ИНН %s";
    
    #[\Override]
    public function getResult(array $params): mixed {
        
        $search = new AIFunctionSearch();
        
        $common_data = $search->getResult([ 'prompt' => sprintf(self::SP_COMMON_DATA, $params['inn'])]);
        $contract_stat = $search->getResult([ 'prompt' => sprintf(self::SP_CONTRACT_STAT, $params['inn'])]);
        $dinamics = $search->getResult([ 'prompt' => sprintf(self::SP_DINAMICS, $params['inn'])]);
        $records = $search->getResult([ 'prompt' => sprintf(self::SP_RECORDS, $params['inn'])]);
        $spec = $search->getResult([ 'prompt' => sprintf(self::SP_SPEC, $params['inn'])]);
        return "$common_data\n\n$contract_stat\n\n$dinamics\n\n$records\n\n$spec";
    }
}
