<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\BlagoBot\data\report_param_value;
use losthost\BlagoBot\service\xls\Column;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\DB\DB;
use losthost\BlagoBot\service\ReportSummary;

class ReportGP extends AbstractReport {

    const ID_FB = 97;
    const ID_BM = 98;
    const ID_BMO = 99;
    const ID_OMSU = 100;
    const ID_OMSU2 = 101;
    
    protected bool $show_fb    = false;
    protected bool $show_bm    = false;
    protected bool $show_bmo   = false;
    protected bool $show_omsu  = false;
    protected bool $show_omsu2 = false;

    protected function checkParamErrors($params): false|array {

        foreach ($params['sources'] as $value) {
            switch ($value) {
                case self::ID_FB:
                    $this->show_fb = true;
                    break;
                case self::ID_BM:
                    $this->show_bm = true;
                    break;
                case self::ID_BMO:
                    $this->show_bmo = true;
                    break;
                case self::ID_OMSU:
                    $this->show_omsu = true;
                    break;
                case self::ID_OMSU2:
                    $this->show_omsu2 = true;
                    break;
            }
        }

        return false;
    }

    protected function reportColumns(): array {

        $totals_format = [CellFormat::GeneralTotal, CellFormat::GeneralSubtotal];
        
        $columns = [
            new Column('№ пп', 0, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format),
            new Column('Мероприятие', 40, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format),
            new Column('Завершение', 17, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format, false, null, 1),
            new Column('Кол-во объектов', 12, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true),
            new Column('Кол-во СМР', 10, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true),
            new Column('Кол-во ПИР', 10, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true),
            new Column('Лимит Всего', 20, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true),
        ];
        
        if ($this->show_fb) {
            $columns[] = new Column('Лимит ФБ', 20, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('Лимит БМ', 20, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('Лимит БМО', 20, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('Лимит ОМСУ', 20, CellFormat::GeneralTH, CellFormat::SummTD, $totals_format, true);
        }
        
        $columns[] = new Column('РГ СМР', 7, CellFormat::RgTH, CellFormat::RgTD, $totals_format, true);
        $columns[] = new Column('РГ ПИР', 7, CellFormat::RgTH, CellFormat::SummTD, $totals_format, true);
        $columns[] = new Column('РГ Всего', 20, CellFormat::RgTH, CellFormat::SummTD, $totals_format, true);
        if ($this->show_fb) {
            $columns[] = new Column('РГ ФБ', 20, CellFormat::RgTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('РГ БМ', 20, CellFormat::RgTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('РГ БМО', 20, CellFormat::RgTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('РГ ОМСУ', 20, CellFormat::RgTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu2) {
            $columns[] = new Column('РГ ОМСУ2', 20, CellFormat::RgTH, CellFormat::SummTD, $totals_format, true);
        }
        
        $columns[] = new Column('Публикация СМР', 7, CellFormat::PubishTH, CellFormat::PublishTD, $totals_format, true);
        $columns[] = new Column('Публикация ПИР', 7, CellFormat::PubishTH, CellFormat::SummTD, $totals_format, true);
        $columns[] = new Column('Публикация Всего', 20, CellFormat::PubishTH, CellFormat::SummTD, $totals_format, true);
        if ($this->show_fb) {
            $columns[] = new Column('Публикация ФБ', 20, CellFormat::PubishTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('Публикация БМ', 20, CellFormat::PubishTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('Публикация БМО', 20, CellFormat::PubishTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('Публикация ОМСУ', 20, CellFormat::PubishTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu2) {
            $columns[] = new Column('Публикация ОМСУ2', 20, CellFormat::PubishTH, CellFormat::SummTD, $totals_format, true);
        }
        
        $columns[] = new Column('Контракты СМР', 7, CellFormat::ContractTH, CellFormat::ContractTD, $totals_format, true);
        $columns[] = new Column('Контракты ПИР', 7, CellFormat::ContractTH, CellFormat::SummTD, $totals_format, true);
        $columns[] = new Column('Контракты Всего', 20, CellFormat::ContractTH, CellFormat::SummTD, $totals_format, true);
        if ($this->show_fb) {
            $columns[] = new Column('Контракты ФБ', 20, CellFormat::ContractTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('Контракты БМ', 20, CellFormat::ContractTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('Контракты БМО', 20, CellFormat::ContractTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('Контракты ОМСУ', 20, CellFormat::ContractTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu2) {
            $columns[] = new Column('Контракты ОМСУ2', 20, CellFormat::ContractTH, CellFormat::SummTD, $totals_format, true);
        }

        $columns[] = new Column('Заявки Всего', 20, CellFormat::OrderTH, CellFormat::OrderTD, $totals_format, true);
        if ($this->show_fb) {
            $columns[] = new Column('Заявки ФБ', 20, CellFormat::OrderTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('Заявки БМ', 20, CellFormat::OrderTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('Заявки БМО', 20, CellFormat::OrderTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('Заявки ОМСУ', 20, CellFormat::OrderTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu2) {
            $columns[] = new Column('Заявки ОМСУ2', 20, CellFormat::OrderTH, CellFormat::SummTD, $totals_format, true);
        }

        $columns[] = new Column('Оплата Всего', 20, CellFormat::PaymentTH, CellFormat::PaymentTD, $totals_format, true);
        if ($this->show_fb) {
            $columns[] = new Column('Оплата ФБ', 20, CellFormat::PaymentTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('Оплата БМ', 20, CellFormat::PaymentTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('Оплата БМО', 20, CellFormat::PaymentTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('Оплата ОМСУ', 20, CellFormat::PaymentTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu2) {
            $columns[] = new Column('Оплата ОМСУ2', 20, CellFormat::PaymentTH, CellFormat::SummTD, $totals_format, true);
        }

        $columns[] = new Column('Остатки по итогам торгов Всего', 20, CellFormat::RestTH, CellFormat::RestTD, $totals_format, true);
        if ($this->show_fb) {
            $columns[] = new Column('Остатки по итогам торгов ФБ', 20, CellFormat::RestTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('Остатки по итогам торгов БМ', 20, CellFormat::RestTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('Остатки по итогам торгов БМО', 20, CellFormat::RestTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('Остатки по итогам торгов ОМСУ', 20, CellFormat::RestTH, CellFormat::SummTD, $totals_format, true);
        }

        $columns[] = new Column('Остатки по итогам освоения Всего', 20, CellFormat::ToPayTH, CellFormat::ToPayTD, $totals_format, true);
        if ($this->show_fb) {
            $columns[] = new Column('Остатки по итогам освоения ФБ', 20, CellFormat::ToPayTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bm) {
            $columns[] = new Column('Остатки по итогам освоения БМ', 20, CellFormat::ToPayTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_bmo) {
            $columns[] = new Column('Остатки по итогам освоения БМО', 20, CellFormat::ToPayTH, CellFormat::SummTD, $totals_format, true);
        }
        if ($this->show_omsu) {
            $columns[] = new Column('Остатки по итогам освоения ОМСУ', 20, CellFormat::ToPayTH, CellFormat::SummTD, $totals_format, true);
        }

        return $columns;
    }

    protected function reportData($params): array {
                
        $sql = $this->getSqlQuery();
        
        $period = (new report_param_value(['id' => $params['period'][0]]))->value;
        $sql = str_replace('{:current_year}', $period, $sql);

        if (!$this->show_fb) {
            $sql = preg_replace("/\/\*\* fb \>\> \*\*\/.*?\/\*\* \<\< fb \*\*\//s", '', $sql);
        }
        if (!$this->show_bm) {
            $sql = preg_replace("/\/\*\* bm \>\> \*\*\/.*?\/\*\* \<\< bm \*\*\//s", '', $sql);
        }
        if (!$this->show_bmo) {
            $sql = preg_replace("/\/\*\* bmo \>\> \*\*\/.*?\/\*\* \<\< bmo \*\*\//s", '', $sql);
        }
        if (!$this->show_omsu) {
            $sql = preg_replace("/\/\*\* omsu \>\> \*\*\/.*?\/\*\* \<\< omsu \*\*\//s", '', $sql);
        }
        if (!$this->show_omsu2) {
            $sql = preg_replace("/\/\*\* omsu2 \>\> \*\*\/.*?\/\*\* \<\< omsu2 \*\*\//s", '', $sql);
        }
        
        $sth = DB::prepare($sql);
        $sth->execute();
        error_log($sth->queryString);
        
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        return $sth->fetchAll(\PDO::FETCH_NUM);
        
    }

    protected function reportSummary($params): \losthost\BlagoBot\service\ReportSummary {

        $period = (new report_param_value(['id' => $params['period'][0]]))->value;
        
        return new ReportSummary(
                "Статус реализации мероприятий по ГП \"Формирование современной комфортной городской среды\" в $period году", 
                date_create_immutable(), 
                [
                    ['title' => 'Год', 'value' => $period]
                ]
                );
        
    }

    protected function resultType(): int {
        return AbstractReport::RESULT_TYPE_XLSX;
    }
    
    protected function getSqlQuery() {
        return <<<FIN
        DROP TEMPORARY TABLE IF EXISTS vt_limits_other_years;
        DROP TEMPORARY TABLE IF EXISTS vt_objects;
        DROP TEMPORARY TABLE IF EXISTS vt_contract_data;
        DROP TEMPORARY TABLE IF EXISTS vt_result;

        CREATE TEMPORARY TABLE vt_contract_data SELECT 
                contract.id AS contract_id,
                contract.x_object_id AS object_id,
                CASE 
                	WHEN contract.status = 'РГ' THEN 1
                	WHEN contract.status = 'Закупки' THEN 2
                	WHEN contract.status = 'Контракт' THEN 3
                	ELSE 0
                END AS status,
                contract.has_pir AS has_pir,
                contract.has_smr AS has_smr,
                CASE
                        WHEN contract.status = 'РГ' THEN contract.has_pir 
                        ELSE 0
                END AS rg_pir,
                CASE
                        WHEN contract.status = 'РГ' THEN contract.has_smr 
                        ELSE 0
                END AS rg_smr,
                CASE
                        WHEN contract.status = 'РГ' THEN IFNULL(contract_fb_rg.value, 0) 
                        ELSE 0
                END AS fb_rg_current,
                CASE
                        WHEN contract.status = 'РГ' THEN IFNULL(contract_bm_rg.value, 0) 
                        ELSE 0
                END AS bm_rg_current,
                CASE
                        WHEN contract.status = 'РГ' THEN IFNULL(contract_bmo_rg.value, 0) 
                        ELSE 0
                END AS bmo_rg_current,
                CASE
                        WHEN contract.status = 'РГ' THEN IFNULL(contract_omsu_rg.value, 0) 
                        ELSE 0
                END AS omsu_rg_current,
                CASE
                        WHEN contract.status = 'РГ' THEN IFNULL(contract_omsu2_rg.value, 0) 
                        ELSE 0
                END AS omsu2_rg_current,
                CASE
                        WHEN contract.status = 'Закупки' THEN contract.has_pir 
                        ELSE 0
                END AS nmck_pir,
                CASE
                        WHEN contract.status = 'Закупки' THEN contract.has_smr 
                        ELSE 0
                END AS nmck_smr,
                CASE
                        WHEN contract.status = 'Закупки' THEN IFNULL(contract_fb_nmck.value, 0) 
                        ELSE 0
                END AS fb_nmck_current,
                CASE
                        WHEN contract.status = 'Закупки' THEN IFNULL(contract_bm_nmck.value, 0) 
                        ELSE 0
                END AS bm_nmck_current,
                CASE
                        WHEN contract.status = 'Закупки' THEN IFNULL(contract_bmo_nmck.value, 0) 
                        ELSE 0
                END AS bmo_nmck_current,
                CASE
                        WHEN contract.status = 'Закупки' THEN IFNULL(contract_omsu_nmck.value, 0) 
                        ELSE 0
                END AS omsu_nmck_current,
                CASE
                        WHEN contract.status = 'Закупки' THEN IFNULL(contract_omsu2_nmck.value, 0) 
                        ELSE 0
                END AS omsu2_nmck_current,
                CASE
                        WHEN contract.status = 'Контракт' THEN contract.has_pir 
                        ELSE 0
                END AS contract_pir,
                CASE
                        WHEN contract.status = 'Контракт' THEN contract.has_smr 
                        ELSE 0
                END AS contract_smr,
                CASE
                        WHEN contract.status = 'Контракт' THEN IFNULL(contract_fb_contract.value, 0) 
                        ELSE 0
                END AS fb_contract_current,
                CASE
                        WHEN contract.status = 'Контракт' THEN IFNULL(contract_bm_contract.value, 0) 
                        ELSE 0
                END AS bm_contract_current,
                CASE
                        WHEN contract.status = 'Контракт' THEN IFNULL(contract_bmo_contract.value, 0) 
                        ELSE 0
                END AS bmo_contract_current,
                CASE
                        WHEN contract.status = 'Контракт' THEN IFNULL(contract_omsu_contract.value, 0) 
                        ELSE 0
                END AS omsu_contract_current,
                CASE
                        WHEN contract.status = 'Контракт' THEN IFNULL(contract_omsu2_contract.value, 0) 
                        ELSE 0
                END AS omsu2_contract_current,
                IFNULL(contract_fb_order.value, 0) AS fb_order_current,
                IFNULL(contract_bm_order.value, 0) AS bm_order_current,
                IFNULL(contract_bmo_order.value, 0) AS bmo_order_current,
                IFNULL(contract_omsu_order.value, 0) AS omsu_order_current,
                IFNULL(contract_omsu2_order.value, 0) AS omsu2_order_current,
                IFNULL(contract_fb_payment.value, 0) AS fb_payment_current,
                IFNULL(contract_bm_payment.value, 0) AS bm_payment_current,
                IFNULL(contract_bmo_payment.value, 0) AS bmo_payment_current,
                IFNULL(contract_omsu_payment.value, 0) AS omsu_payment_current,
                IFNULL(contract_omsu2_payment.value, 0) AS omsu2_payment_current
        FROM 
                [x_contract] AS contract
                LEFT JOIN [x_contract_data] AS contract_fb_rg ON contract_fb_rg.type = 'РГ ФБ' AND contract.id = contract_fb_rg.x_contract_id AND contract_fb_rg.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bm_rg ON contract_bm_rg.type = 'РГ БМ' AND contract.id = contract_bm_rg.x_contract_id AND contract_bm_rg.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bmo_rg ON contract_bmo_rg.type = 'РГ БМО' AND contract.id = contract_bmo_rg.x_contract_id AND contract_bmo_rg.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu_rg ON contract_omsu_rg.type = 'РГ ОМСУ' AND contract.id = contract_omsu_rg.x_contract_id AND contract_omsu_rg.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu2_rg ON contract_omsu2_rg.type = 'РГ ОМСУ2' AND contract.id = contract_omsu2_rg.x_contract_id AND contract_omsu2_rg.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_fb_nmck ON contract_fb_nmck.type = 'Нмцк ФБ' AND contract.id = contract_fb_nmck.x_contract_id AND contract_fb_nmck.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bm_nmck ON contract_bm_nmck.type = 'Нмцк БМ' AND contract.id = contract_bm_nmck.x_contract_id AND contract_bm_nmck.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bmo_nmck ON contract_bmo_nmck.type = 'Нмцк БМО' AND contract.id = contract_bmo_nmck.x_contract_id AND contract_bmo_nmck.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu_nmck ON contract_omsu_nmck.type = 'Нмцк ОМСУ' AND contract.id = contract_omsu_nmck.x_contract_id AND contract_omsu_nmck.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu2_nmck ON contract_omsu2_nmck.type = 'Нмцк ОМСУ2' AND contract.id = contract_omsu2_nmck.x_contract_id AND contract_omsu2_nmck.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_fb_contract ON contract_fb_contract.type = 'Контракт ФБ' AND contract.id = contract_fb_contract.x_contract_id AND contract_fb_contract.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bm_contract ON contract_bm_contract.type = 'Контракт БМ' AND contract.id = contract_bm_contract.x_contract_id AND contract_bm_contract.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bmo_contract ON contract_bmo_contract.type = 'Контракт БМО' AND contract.id = contract_bmo_contract.x_contract_id AND contract_bmo_contract.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu_contract ON contract_omsu_contract.type = 'Контракт ОМСУ' AND contract.id = contract_omsu_contract.x_contract_id AND contract_omsu_contract.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu2_contract ON contract_omsu2_contract.type = 'Контракт ОМСУ2' AND contract.id = contract_omsu2_contract.x_contract_id AND contract_omsu2_contract.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_fb_order ON contract_fb_order.type = 'Заявка ФБ' AND contract.id = contract_fb_order.x_contract_id AND contract_fb_order.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bm_order ON contract_bm_order.type = 'Заявка БМ' AND contract.id = contract_bm_order.x_contract_id AND contract_bm_order.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bmo_order ON contract_bmo_order.type = 'Заявка БМО' AND contract.id = contract_bmo_order.x_contract_id AND contract_bmo_order.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu_order ON contract_omsu_order.type = 'Заявка ОМСУ' AND contract.id = contract_omsu_order.x_contract_id AND contract_omsu_order.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu2_order ON contract_omsu2_order.type = 'Заявка ОМСУ2' AND contract.id = contract_omsu2_order.x_contract_id AND contract_omsu2_order.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_fb_payment ON contract_fb_payment.type = 'Оплата ФБ' AND contract.id = contract_fb_payment.x_contract_id AND contract_fb_payment.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bm_payment ON contract_bm_payment.type = 'Оплата БМ' AND contract.id = contract_bm_payment.x_contract_id AND contract_bm_payment.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_bmo_payment ON contract_bmo_payment.type = 'Оплата БМО' AND contract.id = contract_bmo_payment.x_contract_id AND contract_bmo_payment.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu_payment ON contract_omsu_payment.type = 'Оплата ОМСУ' AND contract.id = contract_omsu_payment.x_contract_id AND contract_omsu_payment.year = {:current_year}
                LEFT JOIN [x_contract_data] AS contract_omsu2_payment ON contract_omsu2_payment.type = 'Оплата ОМСУ2' AND contract.id = contract_omsu2_payment.x_contract_id AND contract_omsu2_payment.year = {:current_year}
        WHERE 
                contract.status <> 'Прочее';

        CREATE TEMPORARY TABLE vt_limits_other_years SELECT
                x_object_id AS object_id,
                type AS type,
                SUM(value) AS value
        FROM 
                [x_year_data]
        WHERE 
                year > {:current_year}
        GROUP BY
                object_id,
                type;

        CREATE TEMPORARY TABLE vt_objects SELECT
                object.id AS object_id,
                object.name AS object_name,
                object.x_category_id AS category_id,

                IFNULL(fb_current_year.value, 0) AS fb_current,
                IFNULL(bm_current_year.value, 0) AS bm_current,
                IFNULL(bmo_current_year.value, 0) AS bmo_current,
                IFNULL(omsu_current_year.value, 0) AS omsu_current,
                IFNULL(fb_current_year.value, 0) + IFNULL(bm_current_year.value, 0) + IFNULL(bmo_current_year.value, 0) + IFNULL(omsu_current_year.value, 0) AS total_current,

                IFNULL(pir_current_year.value, 0) AS pir_current,
                IFNULL(smr_current_year.value, 0) AS smr_current,

                IFNULL(fb_other_years.value, 0) AS fb_other,
                IFNULL(bm_other_years.value, 0) AS bm_other,
                IFNULL(bmo_other_years.value, 0) AS bmo_other,
                IFNULL(omsu_other_years.value, 0) AS omsu_other,
                IFNULL(fb_other_years.value, 0) + IFNULL(bm_other_years.value, 0) + IFNULL(bmo_other_years.value, 0) + IFNULL(omsu_other_years.value, 0) AS total_other,

                CASE
                    WHEN MAX(cd.status) = 1 THEN MAX(cd.rg_pir)
                    ELSE 0
                END AS rg_pir,
                CASE
                    WHEN MAX(cd.status) = 1 THEN MAX(cd.rg_smr)
                    ELSE 0
                END AS rg_smr,

                SUM(cd.fb_rg_current) AS fb_rg,
                SUM(cd.bm_rg_current) AS bm_rg,
                SUM(cd.bmo_rg_current) AS bmo_rg,
                SUM(cd.omsu_rg_current) AS omsu_rg,
                SUM(cd.omsu2_rg_current) AS omsu2_rg,
                SUM(cd.fb_rg_current) + SUM(cd.bm_rg_current) + SUM(cd.bmo_rg_current) + SUM(cd.omsu_rg_current) + SUM(cd.omsu2_rg_current) AS total_rg,

                CASE
                    WHEN MAX(cd.status) = 2 THEN MAX(cd.nmck_pir)
                    ELSE 0
                END AS nmck_pir,
                CASE
                    WHEN MAX(cd.status) = 2 THEN MAX(cd.nmck_smr)
                    ELSE 0
                END AS nmck_smr,

                SUM(cd.fb_nmck_current) AS fb_nmck,
                SUM(cd.bm_nmck_current) AS bm_nmck,
                SUM(cd.bmo_nmck_current) AS bmo_nmck,
                SUM(cd.omsu_nmck_current) AS omsu_nmck,
                SUM(cd.omsu2_nmck_current) AS omsu2_nmck,
                SUM(cd.fb_nmck_current) + SUM(cd.bm_nmck_current) + SUM(cd.bmo_nmck_current) + SUM(cd.omsu_nmck_current) + SUM(cd.omsu2_nmck_current) AS total_nmck,

                CASE
                    WHEN MAX(cd.status) = 3 THEN MAX(cd.contract_pir)
                    ELSE 0
                END AS contract_pir,
                CASE
                    WHEN MAX(cd.status) = 3 THEN MAX(cd.contract_smr)
                    ELSE 0
                END AS contract_smr,

                SUM(cd.fb_contract_current) AS fb_contract,
                SUM(cd.bm_contract_current) AS bm_contract,
                SUM(cd.bmo_contract_current) AS bmo_contract,
                SUM(cd.omsu_contract_current) AS omsu_contract,
                SUM(cd.omsu2_contract_current) AS omsu2_contract,
                SUM(cd.fb_contract_current) + SUM(cd.bm_contract_current) + SUM(cd.bmo_contract_current) + SUM(cd.omsu_contract_current) + SUM(cd.omsu2_contract_current) AS total_contract,

                SUM(cd.fb_order_current) AS fb_order,
                SUM(cd.bm_order_current) AS bm_order,
                SUM(cd.bmo_order_current) AS bmo_order,
                SUM(cd.omsu_order_current) AS omsu_order,
                SUM(cd.omsu2_order_current) AS omsu2_order,
                SUM(cd.fb_order_current) + SUM(cd.bm_order_current) + SUM(cd.bmo_order_current) + SUM(cd.omsu_order_current) + SUM(cd.omsu2_order_current) AS total_order,

                SUM(cd.fb_payment_current) AS fb_payment,
                SUM(cd.bm_payment_current) AS bm_payment,
                SUM(cd.bmo_payment_current) AS bmo_payment,
                SUM(cd.omsu_payment_current) AS omsu_payment,
                SUM(cd.omsu2_payment_current) AS omsu2_payment,
                SUM(cd.fb_payment_current) + SUM(cd.bm_payment_current) + SUM(cd.bmo_payment_current) + SUM(cd.omsu_payment_current) + SUM(cd.omsu2_payment_current) AS total_payment
        FROM 
                [x_object] AS object 
                LEFT JOIN [x_year_data] AS fb_current_year ON object.id = fb_current_year.x_object_id AND fb_current_year.type = 'Лимит ФБ' AND fb_current_year.year = {:current_year}
                LEFT JOIN [x_year_data] AS bm_current_year ON object.id = bm_current_year.x_object_id AND bm_current_year.type = 'Лимит БМ' AND bm_current_year.year = {:current_year}
                LEFT JOIN [x_year_data] AS bmo_current_year ON object.id = bmo_current_year.x_object_id AND bmo_current_year.type = 'Лимит БМО' AND bmo_current_year.year = {:current_year}
                LEFT JOIN [x_year_data] AS omsu_current_year ON object.id = omsu_current_year.x_object_id AND omsu_current_year.type = 'Лимит ОМСУ' AND omsu_current_year.year = {:current_year}
                LEFT JOIN [x_year_data] AS pir_current_year ON object.id = pir_current_year.x_object_id AND pir_current_year.type = 'ПИР' AND pir_current_year.year = {:current_year}
                LEFT JOIN [x_year_data] AS smr_current_year ON object.id = smr_current_year.x_object_id AND smr_current_year.type = 'СМР' AND smr_current_year.year = {:current_year}
                LEFT JOIN vt_limits_other_years AS fb_other_years ON object.id = fb_other_years.object_id AND fb_other_years.type = 'Лимит ФБ'
                LEFT JOIN vt_limits_other_years AS bm_other_years ON object.id = bm_other_years.object_id AND bm_other_years.type = 'Лимит БМ'
                LEFT JOIN vt_limits_other_years AS bmo_other_years ON object.id = bmo_other_years.object_id AND bmo_other_years.type = 'Лимит БМО'
                LEFT JOIN vt_limits_other_years AS omsu_other_years ON object.id = omsu_other_years.object_id AND omsu_other_years.type = 'Лимит ОМСУ'
                LEFT JOIN vt_contract_data AS cd ON object.id = cd.object_id

        GROUP BY
                object_id,
                object_name,
                category_id,
                fb_current,
                bm_current,
                bmo_current,
                omsu_current,
                total_current,
                pir_current,
                smr_current

        HAVING 
                total_current > 0;

        CREATE TEMPORARY TABLE vt_result SELECT 
                category.name AS category_name,
                CASE
                        WHEN IFNULL(SUM(object.total_other), 0) > 0 THEN 'буд. года'
                        ELSE '{:current_year}'
                END AS finish,
                COUNT(object.object_id) AS object_count,

                SUM(object.smr_current) AS smr_current,
                SUM(object.pir_current) AS pir_current,

                SUM(object.total_current)/1000 AS sum_current_year,
                /** fb >> **/ 
                SUM(object.fb_current)/1000 AS fb_current,
                /** << fb **/ 
                /** bm >> **/ 
                SUM(object.bm_current)/1000 AS bm_current,
                /** << bm **/ 
                /** bmo >> **/ 
                SUM(object.bmo_current)/1000 AS bmo_current,
                /** << bmo **/ 
                /** omsu >> **/ 
                SUM(object.omsu_current)/1000 AS omsu_current,
                /** << omsu **/ 

                SUM(object.rg_smr) AS rg_smr,
                SUM(object.rg_pir) AS rg_pir,

                SUM(object.total_rg)/1000 AS total_rg, 
                /** fb >> **/ 
                SUM(object.fb_rg)/1000 AS fb_rg, 
                /** << fb **/ 
                /** bm >> **/ 
                SUM(object.bm_rg)/1000 AS bm_rg, 
                /** << bm **/ 
                /** bmo >> **/ 
                SUM(object.bmo_rg)/1000 AS bmo_rg, 
                /** << bmo **/ 
                /** omsu >> **/ 
                SUM(object.omsu_rg)/1000 AS omsu_rg, 
                /** << omsu **/ 
                /** omsu2 >> **/ 
                SUM(object.omsu2_rg)/1000 AS omsu2_rg, 
                /** << omsu2 **/ 

                SUM(object.nmck_smr) AS nmck_smr, 
                SUM(object.nmck_pir) AS nmck_pir,

                SUM(object.total_nmck)/1000 AS total_nmck, 
                /** fb >> **/ 
                SUM(object.fb_nmck)/1000 AS fb_nmck, 
                /** << fb **/ 
                /** bm >> **/ 
                SUM(object.bm_nmck)/1000 AS bm_nmck, 
                /** << bm **/ 
                /** bmo >> **/ 
                SUM(object.bmo_nmck)/1000 AS bmo_nmck, 
                /** << bmo **/ 
                /** omsu >> **/ 
                SUM(object.omsu_nmck)/1000 AS omsu_nmck, 
                /** << omsu **/ 
                /** omsu2 >> **/ 
                SUM(object.omsu2_nmck)/1000 AS omsu2_nmck, 
                /** << omsu2 **/ 

                SUM(object.contract_smr) AS contract_smr,
                SUM(object.contract_pir) AS contract_pir,

                SUM(object.total_contract)/1000 AS total_contract, 
                /** fb >> **/ 
                SUM(object.fb_contract)/1000 AS fb_contract, 
                /** << fb **/ 
                /** bm >> **/ 
                SUM(object.bm_contract)/1000 AS bm_contract, 
                /** << bm **/ 
                /** bmo >> **/ 
                SUM(object.bmo_contract)/1000 AS bmo_contract, 
                /** << bmo **/ 
                /** omsu >> **/ 
                SUM(object.omsu_contract)/1000 AS omsu_contract, 
                /** << omsu **/ 
                /** omsu2 >> **/ 
                SUM(object.omsu2_contract)/1000 AS omsu2_contract, 
                /** << omsu2 **/ 

                SUM(object.total_order)/1000 AS total_order, 
                /** fb >> **/ 
                SUM(object.fb_order)/1000 AS fb_order, 
                /** << fb **/ 
                /** bm >> **/ 
                SUM(object.bm_order)/1000 AS bm_order, 
                /** << bm **/ 
                /** bmo >> **/ 
                SUM(object.bmo_order)/1000 AS bmo_order, 
                /** << bmo **/ 
                /** omsu >> **/ 
                SUM(object.omsu_order)/1000 AS omsu_order, 
                /** << omsu **/ 
                /** omsu2 >> **/ 
                SUM(object.omsu2_order)/1000 AS omsu2_order, 
                /** << omsu2 **/ 

                SUM(object.total_payment)/1000 AS total_payment, 
                /** fb >> **/ 
                SUM(object.fb_payment)/1000 AS fb_payment, 
                /** << fb **/ 
                /** bm >> **/ 
                SUM(object.bm_payment)/1000 AS bm_payment, 
                /** << bm **/ 
                /** bmo >> **/ 
                SUM(object.bmo_payment)/1000 AS bmo_payment, 
                /** << bmo **/ 
                /** omsu >> **/ 
                SUM(object.omsu_payment)/1000 AS omsu_payment, 
                /** << omsu **/ 
                /** omsu2 >> **/ 
                SUM(object.omsu2_payment)/1000 AS omsu2_payment,
                /** << omsu2 **/ 

                (SUM(object.total_current) - SUM(object.total_nmck) - SUM(object.total_contract))/1000 AS total_rest,
                /** fb >> **/ 
                (SUM(object.fb_current) - SUM(object.fb_nmck) - SUM(object.fb_contract))/1000 AS fb_rest,
                /** << fb **/ 
                /** bm >> **/ 
                (SUM(object.bm_current) - SUM(object.bm_nmck) - SUM(object.bm_contract))/1000 AS bm_rest,
                /** << bm **/ 
                /** bmo >> **/ 
                (SUM(object.bmo_current) - SUM(object.bmo_nmck) - SUM(object.bmo_contract))/1000 AS bmo_rest,
                /** << bmo **/ 
                /** omsu >> **/ 
                (SUM(object.omsu_current) - SUM(object.omsu_nmck) - SUM(object.omsu_contract))/1000 AS omsu_rest,
                /** << omsu **/ 

                (SUM(object.total_current) - SUM(object.total_payment))/1000 AS total_topay,
                /** fb >> **/ 
                (SUM(object.fb_current) - SUM(object.fb_payment))/1000 AS fb_topay,
                /** << fb **/ 
                /** bm >> **/ 
                (SUM(object.bm_current) - SUM(object.bm_payment))/1000 AS bm_topay,
                /** << bm **/ 
                /** bmo >> **/ 
                (SUM(object.bmo_current) - SUM(object.bmo_payment))/1000 AS bmo_topay,
                /** << bmo **/ 
                /** omsu >> **/ 
                (SUM(object.omsu_current) - SUM(object.omsu_payment))/1000 AS omsu_topay,
                /** << omsu **/ 
                NULL AS dummy
        FROM 
                [x_category] AS category
                LEFT JOIN vt_objects AS object ON object.category_id = category.id

        GROUP BY
                category.id,
                category.name
        HAVING 
                sum_current_year > 0
        ORDER BY 
                finish ASC,
                sum_current_year DESC;

        SET @row_number = 0;

        SELECT 
                (@row_number:=@row_number + 1) AS num,
                result.*
        FROM vt_result AS result;
        FIN;
    }
}
