<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\view\CustomSentMessagesByOne;
use losthost\DB\DB;
use losthost\BlagoBot\service\ReportSummary;

use function losthost\BlagoBot\__;
use function \losthost\BlagoBot\sendSplitMessage;

class ReportOPZStatus extends AbstractReport {
    
    const SQL_GET_DATA = <<<FIN
            DROP TEMPORARY TABLE IF EXISTS vt_contract_data;
            CREATE TEMPORARY TABLE vt_contract_data 
            SELECT DISTINCT
                    x_contract_id,
                    year,
                    SUM(value) AS value
            FROM [x_contract_data]
            WHERE
                    type IN ('Нмцк ФБ', 'Нмцк БМ', 'Нмцк БМО', 'Нмцк ОМСУ', 'Нмцк ОМСУ2')
            GROUP BY
                    x_contract_id,
                    year	
            ;            

            SELECT 
              contract.nmck_purchase_number,
              object.name,
              contract.status2 AS status,
              CASE
                    WHEN data1.value IS NULL AND data2.value IS NULL THEN :current_year
                    WHEN data2.value IS NULL THEN CONCAT(:current_year, "-", :current_year-1)
                    ELSE CONCAT(:current_year, "-", :current_year-2)
              END AS period,
              CONCAT(REPLACE(FORMAT((data0.value + IFNULL(data1.value, 0) + IFNULL(data2.value, 0)) / 1000, 0), ',', ' '), " тыс. руб.")  AS nmck,
              DATE_FORMAT(contract.nmck_opz_date, '%d.%m.%Y')
            FROM 
              [x_object] AS object
              LEFT JOIN [x_contract] AS contract ON contract.x_object_id = object.id
              LEFT JOIN vt_contract_data AS data0 ON data0.x_contract_id = contract.id AND data0.year = :current_year
              LEFT JOIN vt_contract_data AS data1 ON data1.x_contract_id = contract.id AND data1.year = :current_year+1
              LEFT JOIN vt_contract_data AS data2 ON data2.x_contract_id = contract.id AND data2.year = :current_year+2
            WHERE 
              contract.nmck_purchase_number IS NOT NULL 
              AND contract.nmck_purchase_number NOT IN ('', '0', "'нд") 
              AND data0.value IS NOT NULL
              AND contract.status2 = 'Закупка опубликована'
            FIN;
    
    #[\Override]
    protected function checkParamErrors($params): false|array {
        return false;
    }

    #[\Override]
    protected function initParams() {
        $this->params = [];
    }

    #[\Override]
    protected function reportColumns(): array {
        return [__('№ Закупки'), __('Объект'), __('Статус'), __('Период'), __('НМЦК'), __('ОПЗ')];
    }

    #[\Override]
    protected function reportData($params): array {
        $sql = static::SQL_GET_DATA;
        
        $sth = DB::prepare($sql);
        $sth->execute(['current_year' => $this->getCurrentYear()]);
        
        $sth->nextRowset();
        $sth->nextRowset();
        return $sth->fetchAll();
    }

    #[\Override]
    protected function reportSummary($params): \losthost\BlagoBot\service\ReportSummary {
        return new ReportSummary('Статус ОПЗ', date_create_immutable(), []);        
    }

    protected function getCurrentYear() {
        return date('Y');
    }
    
    #[\Override]
    protected function resultType(): int|string {
        return CustomSentMessagesByOne::class;
    }
}
