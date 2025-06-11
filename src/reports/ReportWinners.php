<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\DB\DB;
use losthost\BlagoBot\service\xls\Column;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\BlagoBot\service\ReportSummary;
use losthost\BlagoBot\data\x_category;
use losthost\BlagoBot\data\x_contragent;
use losthost\DB\DBList;
use losthost\BlagoBot\service\TotalPercentage;
use losthost\BlagoBot\service\TotalTrickyPercentage;

class ReportWinners extends AbstractReport {
    
    const SQL = <<<FIN
            DROP TEMPORARY TABLE IF EXISTS vt_prev;
            DROP TEMPORARY TABLE IF EXISTS vt_result;

            CREATE TEMPORARY TABLE vt_prev SELECT
              year,
              omsu_name,
              object_name,
              category2_name,
              contract_inn, 
              contract_winner,
              SUM(object_count) AS object_count,
              SUM(payment_total) AS payment_total
            FROM 
              [x_prev]
            GROUP BY
              year,
              omsu_name,
              object_name,
              category2_name,
              contract_inn, 
              contract_winner;


            CREATE TEMPORARY TABLE vt_result SELECT
              contragent.name AS winner,
              /* contragent.inn AS inn, */
              omsu.name AS omsu,
              object.name AS object,
              object.category2_name AS category,
              ROUND((IFNULL(bprevyear.payment_total, 0) + IFNULL(prevyear.payment_total, 0) + SUM(IFNULL(contract_fb.value, 0) + IFNULL(contract_bm.value, 0) + IFNULL(contract_bmo.value, 0) + IFNULL(contract_omsu.value, 0) + IFNULL(contract_omsu2.value, 0))) / 1000) AS total,
              ROUND((IFNULL(bprevyear.payment_total, 0)) / 1000) AS payment_bprev,
              ROUND((IFNULL(prevyear.payment_total, 0)) / 1000) AS payment_prev,
              ROUND(SUM(IFNULL(contract_fb.value, 0) + IFNULL(contract_bm.value, 0) + IFNULL(contract_bmo.value, 0) + IFNULL(contract_omsu.value, 0) + IFNULL(contract_omsu2.value, 0)) / 1000) AS contract_current,
              ROUND(SUM(IFNULL(next_contract_fb.value, 0) + IFNULL(next_contract_bm.value, 0) + IFNULL(next_contract_bmo.value, 0) + IFNULL(next_contract_omsu.value, 0) + IFNULL(next_contract_omsu2.value, 0)) / 1000) AS contract_next,
              ROUND(SUM(IFNULL(payment_fb.value, 0) + IFNULL(payment_bm.value, 0) + IFNULL(payment_bmo.value, 0) + IFNULL(payment_omsu.value, 0) + IFNULL(payment_omsu2.value, 0)) / 1000) AS payment_current,
              ROUND(SUM(IFNULL(payment_fb.value, 0) + IFNULL(payment_bm.value, 0) + IFNULL(payment_bmo.value, 0) + IFNULL(payment_omsu.value, 0) + IFNULL(payment_omsu2.value, 0)) / SUM(IFNULL(contract_fb.value, 0) + IFNULL(contract_bm.value, 0) + IFNULL(contract_bmo.value, 0) + IFNULL(contract_omsu.value, 0) + IFNULL(contract_omsu2.value, 0)) * 100, 2) AS dp_current,
              ROUND((SUM(IFNULL(contract_fb.value, 0) + IFNULL(contract_bm.value, 0) + IFNULL(contract_bmo.value, 0) + IFNULL(contract_omsu.value, 0) + IFNULL(contract_omsu2.value, 0)) - SUM(IFNULL(payment_fb.value, 0) + IFNULL(payment_bm.value, 0) + IFNULL(payment_bmo.value, 0) + IFNULL(payment_omsu.value, 0) + IFNULL(payment_omsu2.value, 0))) / 1000) AS payment_rest
            FROM 
              [x_contract] AS contract
              LEFT JOIN [x_contragent] AS contragent ON contragent.id = contract.x_contragent_id
              LEFT JOIN [x_object] AS object ON object.id = contract.x_object_id
              LEFT JOIN [x_omsu] AS omsu ON omsu.id = object.omsu_id
              LEFT JOIN [x_contract_data] AS contract_fb ON contract_fb.year = :current_year AND contract_fb.x_contract_id = contract.id AND contract_fb.type = 'Контракт ФБ'
              LEFT JOIN [x_contract_data] AS contract_bm ON contract_bm.year = :current_year AND contract_bm.x_contract_id = contract.id AND contract_bm.type = 'Контракт БМ'
              LEFT JOIN [x_contract_data] AS contract_bmo ON contract_bmo.year = :current_year AND contract_bmo.x_contract_id = contract.id AND contract_bmo.type = 'Контракт БМО'
              LEFT JOIN [x_contract_data] AS contract_omsu ON contract_omsu.year = :current_year AND contract_omsu.x_contract_id = contract.id AND contract_omsu.type = 'Контракт ОМСУ'
              LEFT JOIN [x_contract_data] AS contract_omsu2 ON contract_omsu2.year = :current_year AND contract_omsu2.x_contract_id = contract.id AND contract_omsu2.type = 'Контракт ОМСУ2'
              LEFT JOIN [x_contract_data] AS payment_fb ON payment_fb.year = :current_year AND payment_fb.x_contract_id = contract.id AND payment_fb.type = 'Оплата ФБ'
              LEFT JOIN [x_contract_data] AS payment_bm ON payment_bm.year = :current_year AND payment_bm.x_contract_id = contract.id AND payment_bm.type = 'Оплата БМ'
              LEFT JOIN [x_contract_data] AS payment_bmo ON payment_bmo.year = :current_year AND payment_bmo.x_contract_id = contract.id AND payment_bmo.type = 'Оплата БМО'
              LEFT JOIN [x_contract_data] AS payment_omsu ON payment_omsu.year = :current_year AND payment_omsu.x_contract_id = contract.id AND payment_omsu.type = 'Оплата ОМСУ'
              LEFT JOIN [x_contract_data] AS payment_omsu2 ON payment_omsu2.year = :current_year AND payment_omsu2.x_contract_id = contract.id AND payment_omsu2.type = 'Оплата ОМСУ2'
              LEFT JOIN vt_prev AS prevyear ON prevyear.year = :prev_year AND contragent.inn = prevyear.contract_inn AND prevyear.object_name = object.name
              LEFT JOIN vt_prev AS bprevyear ON bprevyear.year = :bprev_year AND contragent.inn = bprevyear.contract_inn AND bprevyear.object_name = object.name
              LEFT JOIN [x_contract_data] AS next_contract_fb ON contract_fb.year = :next_year AND contract_fb.x_contract_id = contract.id AND contract_fb.type = 'Контракт ФБ'
              LEFT JOIN [x_contract_data] AS next_contract_bm ON contract_bm.year = :next_year AND contract_bm.x_contract_id = contract.id AND contract_bm.type = 'Контракт БМ'
              LEFT JOIN [x_contract_data] AS next_contract_bmo ON contract_bmo.year = :next_year AND contract_bmo.x_contract_id = contract.id AND contract_bmo.type = 'Контракт БМО'
              LEFT JOIN [x_contract_data] AS next_contract_omsu ON contract_omsu.year = :next_year AND contract_omsu.x_contract_id = contract.id AND contract_omsu.type = 'Контракт ОМСУ'
              LEFT JOIN [x_contract_data] AS next_contract_omsu2 ON contract_omsu2.year = :next_year AND contract_omsu2.x_contract_id = contract.id AND contract_omsu2.type = 'Контракт ОМСУ2'
            WHERE
              contract.x_contragent_id IN (%WINNERS_LIST%)
            GROUP BY
              contragent.name,
              omsu.name,
              object.category2_name,
              object.name
            ORDER BY
              contragent.name, 
              omsu.name,
              object.category2_name,
              object.name;

            SET @row_number = 0;
        
            SELECT 
                (@row_number:=@row_number + 1) AS num,
                result.*
            FROM vt_result AS result;
            
            DROP TEMPORARY TABLE vt_prev;
            DROP TEMPORARY TABLE vt_result;
            
            FIN;
    
    protected function checkParamErrors($params): false|array {
        return false;
    }

    protected function initParams() { 
        $this->params = [
            new \losthost\BlagoBot\params\ParamDescriptionWinners($this),
        ];
    }

    protected function reportColumns(): array {
        
        $fth = CellFormat::GeneralTH;
        $ftd = CellFormat::GeneralTD;
        $fsd = CellFormat::SummTD;
        $fpd = CellFormat::PercentTD;
        $fpt = [CellFormat::PercentTotal, CellFormat::PercentSubtotal];
        $num_width = 12;
        $percent_width = 20;
        
        $totals_format = [CellFormat::GeneralTotal, CellFormat::GeneralSubtotal];
        $year = $this->getCurrentYear();
        
        $columns = [
            new Column('№ пп', 0, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format),
            new Column('Победитель', 20, CellFormat::GeneralTH, $ftd, $totals_format, false, null, 1),
            new Column('ОМСУ', 15, CellFormat::GeneralTH, $ftd, $totals_format),
            new Column('Наименование объекта', 50, CellFormat::GeneralTH, $ftd, $totals_format),
            new Column('Категория', 20, CellFormat::GeneralTH, $ftd, $totals_format),
            new Column('Всего', $num_width, CellFormat::GeneralTH, $fsd, $totals_format, true),
            new Column('Оплаты '. ($year-2), $num_width, CellFormat::GeneralTH, $fsd, $totals_format, true),
            new Column('Оплаты '. ($year-1), $num_width, CellFormat::GeneralTH, $fsd, $totals_format, true),
        ];
        
        $column_total_contract = new Column("Контракт $year", $num_width, CellFormat::GeneralTH, $fsd, $totals_format, true);
        $column_total_payment =  new Column("Оплаты $year", $num_width, CellFormat::GeneralTH, $fsd, $totals_format, true);
        
        $columns[] = $column_total_contract;
        $columns[] = new Column('Контракт '. ($year+1), $num_width, CellFormat::GeneralTH, $fsd, $totals_format, true);
        $columns[] = $column_total_payment;
        $columns[] = new Column("% оплаты", $num_width, CellFormat::GeneralTH, $fsd, [CellFormat::PercentTotal, CellFormat::PercentPaymentSubtotal], new TotalPercentage([$column_total_contract, $column_total_payment]));
        $columns[] = new Column("Остаток оплаты", $num_width, CellFormat::GeneralTH, $fsd, $totals_format, true);

        return $columns;
        
    }

    protected function getCurrentYear() {
        return date('Y');
    }

    protected function reportData($params): array {
        
        $query_params = [
            'current_year' => $this->getCurrentYear(),
            'prev_year' => $this->getCurrentYear()-1,
            'bprev_year' => $this->getCurrentYear()-2,
            'next_year' => $this->getCurrentYear()+1,
        ];
        
        $sql = str_replace('%WINNERS_LIST%', implode(', ', $params['winners']), static::SQL);
        
        $sth = DB::prepare($sql);
        $sth->execute($query_params);
        error_log($sth->queryString);
        
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        return $sth->fetchAll(\PDO::FETCH_NUM);
        
    }

    #[\Override]
    protected function reportSummary($params): \losthost\BlagoBot\service\ReportSummary {
        $winners = [];
        foreach ($params['winners'] as $id) {
            
            $winner = new x_contragent(['id' => $id]);
            $winners[] = $winner->name;
        }
        return new ReportSummary(
                'Победители', 
                date_create_immutable(), 
                [
                    ['title' => 'Победители', 'value' => implode(', ', $winners)]
                ]
                );
    }

    #[\Override]
    protected function resultType(): int|string {
        return self::RESULT_TYPE_XLSX;
    }
}
