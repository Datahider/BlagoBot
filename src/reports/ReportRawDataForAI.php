<?php

namespace losthost\BlagoBot\reports;


class ReportRawDataForAI extends AbstractReport {
    
    const SQL = <<<FIN
            SELECT
                    omsu.name AS omsu,
                    contract.status2 AS status,
                    contract.has_pir AS has_pir,
                    contract.has_smr AS has_smr,
                    obj.uin AS uin,
                    obj.full_name AS full_name,
                    obj.short_name AS short_name,
                    category.name AS category,
                    obj.category2_name AS category2,
                    obj.open_date_planned AS open_date_planned,
                    contragent.name AS contract_winner,
                    contract.date AS contract_date,
                    contract.number AS contract_number

            FROM
                    blago_x_object AS obj
                    LEFT JOIN blago_x_omsu AS omsu
                            ON omsu.id = obj.omsu_id
                    LEFT JOIN blago_x_contract AS contract
                            ON contract.x_object_id = obj.id
                    LEFT JOIN blago_x_category AS category
                            ON category.id = obj.x_category_id
                    LEFT JOIN blago_x_contragent AS contragent
                            ON contragent.id = contract.x_contragent_id
            FIN;
    
    #[\Override]
    protected function checkParamErrors($params): false|array {
        
    }

    #[\Override]
    protected function initParams() {
        
    }

    #[\Override]
    protected function reportColumns(): array {
        
    }

    #[\Override]
    protected function reportData($params): array {
        
    }

    #[\Override]
    protected function reportSummary($params): \losthost\BlagoBot\service\ReportSummary {
        
    }

    #[\Override]
    protected function resultType(): int|string {
        
    }
}
