<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\view\CustomSentMessagesForOPZ;
use losthost\DB\DB;
use losthost\BlagoBot\service\ReportSummary;

use function losthost\BlagoBot\__;
use function \losthost\BlagoBot\sendSplitMessage;

class ReportOPZStatus extends AbstractReport {
    
    const SQL_GET_DATA = <<<FIN
                DROP TEMPORARY TABLE IF EXISTS vt_contract_data;
                CREATE TEMPORARY TABLE vt_contract_data 
                SELECT DISTINCT
                        contract.nmck_purchase_number,
                        contract_data.year,
                        SUM(contract_data.value) AS value,
                        MAX(contract.id) AS x_contract_id,
                        MAX(object.name) AS name,
                        MAX(contract.status2) AS status2,
                        MAX(contract.nmck_opz_date) AS nmck_opz_date
                FROM [x_contract_data] AS contract_data
                LEFT JOIN [x_contract] AS contract ON contract.id = contract_data.x_contract_id
                LEFT JOIN [x_object] AS object ON contract.x_object_id = object.id
                WHERE
                        contract_data.type IN ('Нмцк ФБ', 'Нмцк БМ', 'Нмцк БМО', 'Нмцк ОМСУ', 'Нмцк ОМСУ2')
                        AND contract.status2 = 'Закупка опубликована'
                GROUP BY
                        contract.nmck_purchase_number,
                        contract_data.year	
                ;            

                SELECT DISTINCT 
                  data0.nmck_purchase_number,
                  data0.name,
                  data0.status2 AS status,
                  CASE
                        WHEN data1.value IS NULL AND data2.value IS NULL THEN :current_year
                        WHEN data2.value IS NULL THEN CONCAT(:current_year, "-", :current_year+1)
                        ELSE CONCAT(:current_year, "-", :current_year+2)
                  END AS period,
                  CONCAT(REPLACE(FORMAT((data0.value + IFNULL(data1.value, 0) + IFNULL(data2.value, 0)) / 1000, 0), ',', ' '), " тыс. руб.")  AS nmck,
                  DATE_FORMAT(data0.nmck_opz_date, '%d.%m.%Y'),
                  CASE
                        WHEN data0.nmck_opz_date < CURDATE() THEN 1
                        ELSE 0
                  END AS on_signing
                FROM 
                  [x_object] AS object
                  LEFT JOIN [x_contract] AS contract ON contract.x_object_id = object.id
                  LEFT JOIN vt_contract_data AS data0 ON data0.nmck_purchase_number = contract.nmck_purchase_number AND data0.year = :current_year
                  LEFT JOIN vt_contract_data AS data1 ON data1.nmck_purchase_number = contract.nmck_purchase_number AND data1.year = :current_year+1
                  LEFT JOIN vt_contract_data AS data2 ON data2.nmck_purchase_number = contract.nmck_purchase_number AND data2.year = :current_year+2
                WHERE 
                  contract.nmck_purchase_number IS NOT NULL AND contract.nmck_purchase_number NOT IN ('', '0', "'нд") AND data0.value IS NOT NULL
                  AND contract.status2 = 'Закупка опубликована'
                ORDER BY
                    data0.nmck_opz_date
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
        return CustomSentMessagesForOPZ::class;
    }
}
