<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\DB\DB;

class ReportObjectsByOmsu extends AbstractReport {
    
    const ID_LIMIT_DETAILS = 15;
    const ID_NMCK = 16;
    const ID_CONTRACT = 17;
    const ID_ORDER = 18;
    const ID_PAYMENT = 19;
    const ID_REST = 20;
    const ID_TOPAY = 21;
    
    protected bool $limit_details = false;
    protected bool $nmck          = false;
    protected bool $contract      = false;
    protected bool $order         = false;
    protected bool $payment       = false;
    protected bool $rest          = false;
    protected bool $topay         = false;

    protected function checkParamErrors($params): false|array {
        
        foreach ($params['data'] as $value) {
            switch ($value) {
                case self::ID_LIMIT_DETAILS:
                    $this->limit_details = true;
                    break;
                case self::ID_NMCK:
                    $this->nmck = true;
                    break;
                case self::ID_CONTRACT:
                    $this->contract = true;
                    break;
                case self::ID_ORDER:
                    $this->order = true;
                    break;
                case self::ID_PAYMENT:
                    $this->payment = true;
                    break;
                case self::ID_REST:
                    $this->rest = true;
                    break;
                case self::ID_TOPAY:
                    $this->topay = true;
                    break;
            }
        }
        return false;
    }

    protected function reportColumns(): array {
        
        $columns = [
            '№ пп',
            'ОМСУ',
            'Наименование объекта',
            'Категория',
            'Лимит Всего',
        ];

        if ($this->limit_details) {
            $columns[] = 'Лимит ФБ';
            $columns[] = 'Лимит БМ';
            $columns[] = 'Лимит БМО';
            $columns[] = 'Лимит ОМСУ';
        } 
        
        if ($this->nmck) {
            $columns[] = 'Опубликовано Всего';
            if ($this->limit_details) {
                $columns[] = 'Опубликовано ФБ';
                $columns[] = 'Опубликовано БМ';
                $columns[] = 'Опубликовано БМО';
                $columns[] = 'Опубликовано ОМСУ';
                $columns[] = 'Опубликовано ОМСУ2';
            } 
        }

        if ($this->contract) {
            $columns[] = 'Законтрактовано Всего';
            if ($this->limit_details) {
                $columns[] = 'Законтрактовано ФБ';
                $columns[] = 'Законтрактовано БМ';
                $columns[] = 'Законтрактовано БМО';
                $columns[] = 'Законтрактовано ОМСУ';
                $columns[] = 'Законтрактовано ОМСУ2';
            } 
        }
        
        if ($this->nmck && $this->contract) {
            $columns[] = '% Контрактования / Публикации';
        } elseif ($this->nmck) {
            $columns[] = '% Публикации';
        } elseif ($this->contract) {
            $columns[] = '% Контрактования';
        }
        
        if ($this->order) {
            $columns[] = 'Заявки Всего';
            if ($this->limit_details) {
                $columns[] = 'Заявки ФБ';
                $columns[] = 'Заявки БМ';
                $columns[] = 'Заявки БМО';
                $columns[] = 'Заявки ОМСУ';
                $columns[] = 'Заявки ОМСУ2';
            } 
            $columns[] = '% поданных заявок';
        }
        
        if ($this->payment) {
            $columns[] = 'Оплата Всего';
            if ($this->limit_details) {
                $columns[] = 'Оплата ФБ';
                $columns[] = 'Оплата БМ';
                $columns[] = 'Оплата БМО';
                $columns[] = 'Оплата ОМСУ';
                $columns[] = 'Оплата ОМСУ2';
            } 
            $columns[] = '% Освоения';
        }

        if ($this->rest) {
            $columns[] = 'Остаток от торгов Всего';
            if ($this->limit_details) {
                $columns[] = 'Остаток от торгов ФБ';
                $columns[] = 'Остаток от торгов БМ';
                $columns[] = 'Остаток от торгов БМО';
                $columns[] = 'Остаток от торгов ОМСУ';
            } 
        }

        if ($this->topay) {
            $columns[] = 'Остаток к освоению';
            if ($this->limit_details) {
                $columns[] = 'Остаток к освоению ФБ';
                $columns[] = 'Остаток к освоению БМ';
                $columns[] = 'Остаток к освоению БМО';
                $columns[] = 'Остаток к освоению ОМСУ';
            } 
        }
        
        return $columns;
    }

    protected function reportData($params): array {
        
        return $this->queryReportData($params);
        
        
    }

    protected function resultType(): int {
        return self::RESULT_TYPE_XLSX;
    }
    
    protected function queryReportData($params) {
        
        $sql = $this->getSqlQuery();
        $sql = str_replace('{:current_year}', date('Y'), $sql);
        $sql = str_replace('{:omsu_ids}', implode(',', $params['omsu']), $sql);
        
        if (!$this->limit_details) {
            $sql = preg_replace("/\/\*\* limit details \>\> \*\*\/.*?\/\*\* \<\< limit details \*\*\//s", '', $sql);
        }
        if (!$this->nmck) {
            $sql = preg_replace("/\/\*\* nmck \>\> \*\*\/.*?\/\*\* \<\< nmck \*\*\//s", '', $sql);
            $sql = preg_replace("/\/\*\* nmck and contract \>\> \*\*\/.*?\/\*\* \<\< nmck and contract \*\*\//s", '', $sql);
        } else {
            $sql = preg_replace("/\/\*\* contract only \>\> \*\*\/.*?\/\*\* \<\< contract only \*\*\//s", '', $sql);
        }
        if (!$this->contract) {
            $sql = preg_replace("/\/\*\* contract \>\> \*\*\/.*?\/\*\* \<\< contract \*\*\//s", '', $sql);
            $sql = preg_replace("/\/\*\* nmck and contract \>\> \*\*\/.*?\/\*\* \<\< nmck and contract \*\*\//s", '', $sql);
        } else {
            $sql = preg_replace("/\/\*\* nmck only \>\> \*\*\/.*?\/\*\* \<\< nmck only \*\*\//s", '', $sql);
        }
        if (!$this->order) {
            $sql = preg_replace("/\/\*\* order \>\> \*\*\/.*?\/\*\* \<\< order \*\*\//s", '', $sql);
        }
        if (!$this->payment) {
            $sql = preg_replace("/\/\*\* payment \>\> \*\*\/.*?\/\*\* \<\< payment \*\*\//s", '', $sql);
        }
        if (!$this->rest) {
            $sql = preg_replace("/\/\*\* rest \>\> \*\*\/.*?\/\*\* \<\< rest \*\*\//s", '', $sql);
        }
        if (!$this->topay) {
            $sql = preg_replace("/\/\*\* topay \>\> \*\*\/.*?\/\*\* \<\< topay \*\*\//s", '', $sql);
        }
        
        $sql = preg_replace("/\,\s*?(\/\*\* [^\r\n]*? \*\*\/\s*?)*?\s*?FROM/s", "\nFROM", $sql);
        
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
        $sth->nextRowset();
        $sth->nextRowset();
        return $sth->fetchAll(\PDO::FETCH_NUM);
    }
    
    protected function getSqlQuery() {
        return <<<FIN
            /* DROP TEMPORARY TABLES IF EXIST */
            DROP TEMPORARY TABLE IF EXISTS vt_contract_data;
            DROP TEMPORARY TABLE IF EXISTS vt_contract_sums;
            DROP TEMPORARY TABLE IF EXISTS vt_contract_agregates;
            DROP TEMPORARY TABLE IF EXISTS vt_limits;
            DROP TEMPORARY TABLE IF EXISTS vt_result;

            /* CALCULATE LIMITS BY OBJECT */
            CREATE TEMPORARY TABLE vt_limits SELECT
                    object.id AS object_id,
                    SUM(fb.value) AS fb_limit,
                    SUM(bm.value) AS bm_limit,
                    SUM(bmo.value) AS bmo_limit,
                    SUM(omsu.value) AS omsu_limit
            FROM 
                    [x_object] AS object
                    LEFT JOIN [x_year_data] AS fb ON object.id = fb.x_object_id AND fb.type = "Лимит ФБ" AND fb.year = {:current_year}
                    LEFT JOIN [x_year_data] AS bm ON object.id = bm.x_object_id AND bm.type = "Лимит БМ" AND bm.year = {:current_year}
                    LEFT JOIN [x_year_data] AS bmo ON object.id = bmo.x_object_id AND bmo.type = "Лимит БМО" AND bmo.year = {:current_year}
                    LEFT JOIN [x_year_data] AS omsu ON object.id = omsu.x_object_id AND omsu.type = "Лимит ОМСУ" AND omsu.year = {:current_year}
            GROUP BY 
                    object.id
            HAVING 
                    NOT (fb_limit IS NULL AND bm_limit IS NULL AND bmo_limit IS NULL AND omsu_limit IS NULL);


            /* CALCULATE USED BY CONTRACTS */   
            CREATE TEMPORARY TABLE vt_contract_data SELECT
                    contract.id AS contract_id,
                    contract.x_object_id AS object_id,
                    SUM(nmck_fb.value) AS fb_nmck,
                    SUM(nmck_bm.value) AS bm_nmck,
                    SUM(nmck_bmo.value) AS bmo_nmck,
                    SUM(nmck_omsu.value) AS omsu_nmck,
                    SUM(nmck_omsu2.value) AS omsu2_nmck,
                    SUM(contract_fb.value) AS fb_contract,
                    SUM(contract_bm.value) AS bm_contract,
                    SUM(contract_bmo.value) AS bmo_contract,
                    SUM(contract_omsu.value) AS omsu_contract,
                    SUM(contract_omsu2.value) AS omsu2_contract,
                    SUM(order_fb.value) AS fb_order,
                    SUM(order_bm.value) AS bm_order,
                    SUM(order_bmo.value) AS bmo_order,
                    SUM(order_omsu.value) AS omsu_order,
                    SUM(order_omsu2.value) AS omsu2_order,
                    SUM(payment_fb.value) AS fb_payment,
                    SUM(payment_bm.value) AS bm_payment,
                    SUM(payment_bmo.value) AS bmo_payment,
                    SUM(payment_omsu.value) AS omsu_payment,
                    SUM(payment_omsu2.value) AS omsu2_payment
            FROM
                    [x_contract] AS contract
                    LEFT JOIN [x_contract_data] AS nmck_fb ON contract.id = nmck_fb.x_contract_id AND nmck_fb.type = "Нмцк ФБ" AND nmck_fb.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS nmck_bm ON contract.id = nmck_bm.x_contract_id AND nmck_bm.type = "Нмцк БМ" AND nmck_bm.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS nmck_bmo ON contract.id = nmck_bmo.x_contract_id AND nmck_bmo.type = "Нмцк БМО" AND nmck_bmo.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS nmck_omsu ON contract.id = nmck_omsu.x_contract_id AND nmck_omsu.type = "Нмцк ОМСУ" AND nmck_omsu.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS nmck_omsu2 ON contract.id = nmck_omsu2.x_contract_id AND nmck_omsu2.type = "Нмцк ОМСУ2" AND nmck_omsu2.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS contract_fb ON contract.id = contract_fb.x_contract_id AND contract_fb.type = "Контракт ФБ" AND contract_fb.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS contract_bm ON contract.id = contract_bm.x_contract_id AND contract_bm.type = "Контракт БМ" AND contract_bm.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS contract_bmo ON contract.id = contract_bmo.x_contract_id AND contract_bmo.type = "Контракт БМО" AND contract_bmo.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS contract_omsu ON contract.id = contract_omsu.x_contract_id AND contract_omsu.type = "Контракт ОМСУ" AND contract_omsu.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS contract_omsu2 ON contract.id = contract_omsu2.x_contract_id AND contract_omsu2.type = "Контракт ОМСУ2" AND contract_omsu2.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS order_fb ON contract.id = order_fb.x_contract_id AND order_fb.type = "Заявка ФБ" AND order_fb.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS order_bm ON contract.id = order_bm.x_contract_id AND order_bm.type = "Заявка БМ" AND order_bm.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS order_bmo ON contract.id = order_bmo.x_contract_id AND order_bmo.type = "Заявка БМО" AND order_bmo.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS order_omsu ON contract.id = order_omsu.x_contract_id AND order_omsu.type = "Заявка ОМСУ" AND order_omsu.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS order_omsu2 ON contract.id = order_omsu2.x_contract_id AND order_omsu2.type = "Заявка ОМСУ2" AND order_omsu2.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS payment_fb ON contract.id = payment_fb.x_contract_id AND payment_fb.type = "Оплата ФБ" AND payment_fb.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS payment_bm ON contract.id = payment_bm.x_contract_id AND payment_bm.type = "Оплата БМ" AND payment_bm.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS payment_bmo ON contract.id = payment_bmo.x_contract_id AND payment_bmo.type = "Оплата БМО" AND payment_bmo.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS payment_omsu ON contract.id = payment_omsu.x_contract_id AND payment_omsu.type = "Оплата ОМСУ" AND payment_omsu.year = {:current_year}
                    LEFT JOIN [x_contract_data] AS payment_omsu2 ON contract.id = payment_omsu2.x_contract_id AND payment_omsu2.type = "Оплата ОМСУ2" AND payment_omsu2.year = {:current_year}
            GROUP BY 
                    contract.id
            HAVING 
                    NOT (
                            fb_nmck IS NULL AND bm_nmck IS NULL AND bmo_nmck IS NULL AND omsu_nmck IS NULL AND omsu2_nmck IS NULL
                            AND fb_contract IS NULL AND bm_contract IS NULL AND bmo_contract IS NULL AND omsu_contract IS NULL AND omsu2_contract IS NULL
                    );

            /* CALCULATE USED BY CONTRACT SUMS */
            CREATE TEMPORARY TABLE vt_contract_sums SELECT
                    contract_id,
                    object_id,
                    CASE 
                            WHEN fb_contract IS NULL THEN fb_nmck
                            ELSE NULL
                    END AS fb_nmck,
                    CASE 
                            WHEN bm_contract IS NULL THEN bm_nmck
                            ELSE NULL
                    END AS bm_nmck,
                    CASE 
                            WHEN bmo_contract IS NULL THEN bmo_nmck
                            ELSE NULL
                    END AS bmo_nmck,
                    CASE 
                            WHEN omsu_contract IS NULL THEN omsu_nmck
                            ELSE NULL
                    END AS omsu_nmck,
                    CASE 
                           WHEN omsu2_contract IS NULL THEN omsu2_nmck
                           ELSE NULL
                    END AS omsu2_nmck,
                    fb_contract,
                    bm_contract,
                    bmo_contract,
                    omsu_contract,	
                    omsu2_contract,	
                    fb_order,
                    bm_order,
                    bmo_order,
                    omsu_order,	
                    omsu2_order,	
                    fb_payment,
                    bm_payment,
                    bmo_payment,
                    omsu_payment,	
                    omsu2_payment	
            FROM 
                    vt_contract_data;

            CREATE TEMPORARY TABLE vt_contract_agregates SELECT
                    object_id,
                    SUM(fb_nmck) AS fb_nmck,
                    SUM(bm_nmck) AS bm_nmck,
                    SUM(bmo_nmck) AS bmo_nmck,
                    SUM(omsu_nmck) AS omsu_nmck,
                    SUM(omsu2_nmck) AS omsu2_nmck,
                    SUM(fb_contract) AS fb_contract,
                    SUM(bm_contract) AS bm_contract,
                    SUM(bmo_contract) AS bmo_contract,
                    SUM(omsu_contract) AS omsu_contract,
                    SUM(omsu2_contract) AS omsu2_contract,
                    SUM(fb_order) AS fb_order,
                    SUM(bm_order) AS bm_order,
                    SUM(bmo_order) AS bmo_order,
                    SUM(omsu_order) AS omsu_order,
                    SUM(omsu2_order) AS omsu2_order,
                    SUM(fb_payment) AS fb_payment,
                    SUM(bm_payment) AS bm_payment,
                    SUM(bmo_payment) AS bmo_payment,
                    SUM(omsu_payment) AS omsu_payment,
                    SUM(omsu2_payment) AS omsu2_payment
            FROM 
                    vt_contract_sums
            GROUP BY 
                    object_id;	

            CREATE TEMPORARY TABLE vt_result SELECT 
                    omsu.name AS omsu,
                    object.name AS name,
                    category.name AS category,
                    CASE
                            WHEN IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0) = 0 THEN NULL
                            ELSE (IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0)) / 1000
                    END AS total_limit,
                    /** limit details >> **/
                    limits.fb_limit / 1000 AS fb_limit,
                    limits.bm_limit / 1000 AS bm_limit,
                    limits.bmo_limit / 1000 AS bmo_limit,
                    limits.omsu_limit / 1000 AS omsu_limit,
                    /** << limit details **/
                    /** nmck >> **/
                    CASE 
                            WHEN IFNULL(contract.fb_nmck, 0) + IFNULL(contract.bm_nmck, 0) + IFNULL(contract.bmo_nmck, 0) + IFNULL(contract.omsu_nmck, 0) + IFNULL(contract.omsu2_nmck, 0) = 0 THEN NULL
                            ELSE (IFNULL(contract.fb_nmck, 0) + IFNULL(contract.bm_nmck, 0) + IFNULL(contract.bmo_nmck, 0) + IFNULL(contract.omsu_nmck, 0) + IFNULL(contract.omsu2_nmck, 0)) / 1000
                    END AS total_nmck,
                    /** limit details >> **/
                    contract.fb_nmck / 1000 AS fb_nmck,
                    contract.bm_nmck / 1000 AS bm_nmck,
                    contract.bmo_nmck / 1000 AS bmo_nmck,
                    contract.omsu_nmck / 1000 AS omsu_nmck,
                    contract.omsu2_nmck / 1000 AS omsu2_nmck,
                    /** << limit details **/
                    /** << nmck **/
                    /** contract >> **/
                    CASE
                            WHEN IFNULL(contract.fb_contract, 0) + IFNULL(contract.bm_contract, 0) + IFNULL(contract.bmo_contract, 0) + IFNULL(contract.omsu_contract, 0) + IFNULL(contract.omsu2_contract, 0) = 0 THEN NULL
                            ELSE (IFNULL(contract.fb_contract, 0) + IFNULL(contract.bm_contract, 0) + IFNULL(contract.bmo_contract, 0) + IFNULL(contract.omsu_contract, 0) + IFNULL(contract.omsu2_contract, 0)) / 1000
                    END AS total_contract,
                    /** limit details >> **/
                    contract.fb_contract / 1000 AS fb_contract,
                    contract.bm_contract / 1000 AS bm_contract,
                    contract.bmo_contract / 1000 AS bmo_contract,
                    contract.omsu_contract / 1000 AS omsu_contract,
                    contract.omsu2_contract / 1000 AS omsu2_contract,
                    /** << limit details **/
                    /** << contract **/
                    /** nmck and contract >> **/
                    (
                            IFNULL(contract.fb_nmck, 0) + IFNULL(contract.bm_nmck, 0) + IFNULL(contract.bmo_nmck, 0) + IFNULL(contract.omsu_nmck, 0) + 
                            IFNULL(contract.fb_contract, 0) + IFNULL(contract.bm_contract, 0) + IFNULL(contract.bmo_contract, 0) + IFNULL(contract.omsu_contract, 0)
                    ) / (IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0)) AS percent_nc,
                    /** << nmck and contract **/
                    /** nmck only >> **/
                    (
                            IFNULL(contract.fb_nmck, 0) + IFNULL(contract.bm_nmck, 0) + IFNULL(contract.bmo_nmck, 0) + IFNULL(contract.omsu_nmck, 0) 
                    ) / (IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0)) AS percent_nc,
                    /** << nmck only **/
                    /** contract only >> **/
                    (
                            IFNULL(contract.fb_contract, 0) + IFNULL(contract.bm_contract, 0) + IFNULL(contract.bmo_contract, 0) + IFNULL(contract.omsu_contract, 0)
                    ) / (IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0)) AS percent_nc,
                    /** << contract only **/
                    /** order >> **/
                    CASE
                            WHEN IFNULL(contract.fb_order, 0) + IFNULL(contract.bm_order, 0) + IFNULL(contract.bmo_order, 0) + IFNULL(contract.omsu_order, 0) + IFNULL(contract.omsu2_order, 0) = 0 THEN NULL
                            ELSE (IFNULL(contract.fb_order, 0) + IFNULL(contract.bm_order, 0) + IFNULL(contract.bmo_order, 0) + IFNULL(contract.omsu_order, 0) + IFNULL(contract.omsu2_order, 0)) / 1000
                    END AS total_order,
                    /** limit details >> **/
                    contract.fb_order / 1000 AS fb_order,
                    contract.bm_order / 1000 AS bm_order,
                    contract.bmo_order / 1000 AS bmo_order,
                    contract.omsu_order / 1000 AS omsu_order,
                    contract.omsu2_order / 1000 AS omsu2_order,
                    /** << limit details **/
                    (IFNULL(contract.fb_order, 0) + IFNULL(contract.bm_order, 0) + IFNULL(contract.bmo_order, 0) + IFNULL(contract.omsu_order, 0)) 
                            / (IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0)) AS percent_o,
                    /** << order **/
                    /** payment >> **/
                    CASE
                            WHEN IFNULL(contract.fb_payment, 0) + IFNULL(contract.bm_payment, 0) + IFNULL(contract.bmo_payment, 0) + IFNULL(contract.omsu_payment, 0) + IFNULL(contract.omsu2_payment, 0) = 0 THEN NULL
                            ELSE (IFNULL(contract.fb_payment, 0) + IFNULL(contract.bm_payment, 0) + IFNULL(contract.bmo_payment, 0) + IFNULL(contract.omsu_payment, 0) + IFNULL(contract.omsu2_payment, 0)) / 1000
                    END AS total_payment,
                    /** limit details >> **/
                    contract.fb_payment / 1000 AS fb_payment,
                    contract.bm_payment / 1000 AS bm_payment,
                    contract.bmo_payment / 1000 AS bmo_payment,
                    contract.omsu_payment / 1000 AS omsu_payment,
                    contract.omsu2_payment / 1000 AS omsu2_payment,
                    /** << limit details **/
                    (IFNULL(contract.fb_payment, 0) + IFNULL(contract.bm_payment, 0) + IFNULL(contract.bmo_payment, 0) + IFNULL(contract.omsu_payment, 0)) 
                            / (IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0)) AS percent_p,
                    /** << payment **/
                    /** rest >> **/
                    ((IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0)) 
                            - (IFNULL(contract.fb_nmck, 0) + IFNULL(contract.bm_nmck, 0) + IFNULL(contract.bmo_nmck, 0) + IFNULL(contract.omsu_nmck, 0) + IFNULL(contract.omsu2_nmck, 0))
                            - (IFNULL(contract.fb_contract, 0) + IFNULL(contract.bm_contract, 0) + IFNULL(contract.bmo_contract, 0) + IFNULL(contract.omsu_contract, 0) + IFNULL(contract.omsu2_contract, 0))) / 1000 AS rest_total,
                    /** limit details >> **/
                    (IFNULL(limits.fb_limit, 0) - IFNULL(contract.fb_nmck, 0) - IFNULL(contract.fb_contract, 0)) / 1000 AS rest_fb, 	
                    (IFNULL(limits.bm_limit, 0) - IFNULL(contract.bm_nmck, 0) - IFNULL(contract.bm_contract, 0)) / 1000 AS rest_bm, 	
                    (IFNULL(limits.bmo_limit, 0) - IFNULL(contract.bmo_nmck, 0) - IFNULL(contract.bmo_contract, 0)) / 1000 AS rest_bmo, 	
                    (IFNULL(limits.omsu_limit, 0) - IFNULL(contract.omsu_nmck, 0) - IFNULL(contract.omsu_contract, 0)) / 1000 AS rest_omsu,
                    /** << limit details **/
                    /** << rest **/
                    /** topay >> **/
                    ((IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0))
                            - (IFNULL(contract.fb_payment, 0) + IFNULL(contract.bm_payment, 0) + IFNULL(contract.bmo_payment, 0) + IFNULL(contract.omsu_payment, 0) + IFNULL(contract.omsu2_payment, 0))) / 1000 AS topay_total,
                    /** limit details >> **/
                    (IFNULL(limits.fb_limit, 0) - IFNULL(contract.fb_payment, 0)) / 1000 AS topay_fb, 	
                    (IFNULL(limits.bm_limit, 0) - IFNULL(contract.bm_payment, 0)) / 1000 AS topay_bm, 	
                    (IFNULL(limits.bmo_limit, 0) - IFNULL(contract.bmo_payment, 0)) / 1000 AS topay_bmo, 	
                    (IFNULL(limits.omsu_limit, 0) - IFNULL(contract.omsu_payment, 0)) / 1000 AS topay_omsu
                    /** << limit details **/
                    /** << topay **/
            FROM
                    [x_object] as object
                    LEFT JOIN [x_omsu] AS omsu ON omsu.id = object.omsu_id
                    LEFT JOIN [x_category] AS category ON object.x_category_id = category.id
                    LEFT JOIN vt_limits AS limits ON limits.object_id = object.id
                    LEFT JOIN vt_contract_agregates AS contract ON contract.object_id = object.id
            WHERE
                    IFNULL(limits.fb_limit, 0) + IFNULL(limits.bm_limit, 0) + IFNULL(limits.bmo_limit, 0) + IFNULL(limits.omsu_limit, 0) > 0
                    AND omsu.id IN ({:omsu_ids})
            GROUP BY 
                    omsu.name, object.name, category.name
            ORDER BY 
                    omsu.name, total_limit DESC, object.name;
        
            SET @row_number = 0;
        
            SELECT 
                (@row_number:=@row_number + 1) AS num,
                result.*
            FROM vt_result AS result;

            FIN;
    }
}
