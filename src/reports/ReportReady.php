<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\params\ParamDescriptionPeriod;
use losthost\BlagoBot\params\ParamDescriptionOmsuAll;
use losthost\BlagoBot\params\ParamDescriptionCategory2All;
use losthost\BlagoBot\params\ParamDescriptionResponsibleAll;
use losthost\BlagoBot\params\ParamDescriptionRiskyOnly;
use losthost\BlagoBot\params\ParamDescriptionReadyGroupBy;
use losthost\BlagoBot\params\ParamDescriptionReadySortBy;

use losthost\BlagoBot\service\xls\Column;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\DB\DB;
use losthost\BlagoBot\service\ReportSummary;
use losthost\BlagoBot\service\TotalCount;
use losthost\BlagoBot\service\TotalNulls;
use losthost\telle\Bot;

class ReportReady extends AbstractReport {
    
    const SQL_QUERY = <<<FIN
            DROP TEMPORARY TABLE IF EXISTS vt_result;
            CREATE TEMPORARY TABLE vt_result 
            SELECT 
                CONCAT(u.surname, ' ', SUBSTRING(u.name, 1, 1), '.', SUBSTRING(u.fathers_name, 1, 1), '.') AS responsible,
                o.category2_name AS category,
                m.name AS omsu,
                YEAR(o.open_date_planned) AS year,
                o.name AS name,
                DATE_FORMAT(o.open_date_fact, "%d-%m-%Y") AS open_date_fact,
                DATE_FORMAT(o.open_date_planned, "%d-%m-%Y") AS open_date_planned,
                o.ready_percent / 100 AS ready_percent
            FROM
                [x_object] AS o
                LEFT JOIN [x_responsible] AS r ON o.x_responsible_id = r.id
                LEFT JOIN [user] AS u ON r.user_id = u.id
                LEFT JOIN [x_omsu] AS m ON m.id = o.omsu_id
            WHERE
                YEAR(o.open_date_planned) IN (%year%)
                AND o.category2_name IN (%cat2%)
                AND o.x_responsible_id IN (%responsible%)
                AND o.omsu_id IN (%omsu%)
                AND (%risky_only% = 0 OR o.open_date_fact IS NULL AND o.open_date_planned < NOW() OR o.open_date_fact IS NOT NULL AND o.ready_percent < 100)
            ORDER BY    
                %group_order%;
            
            SET @row_number = 0;
            SELECT 
                    (@row_number:=@row_number + 1) AS num,
                    result.*
            FROM vt_result AS result;
            DROP TEMPORARY TABLE IF EXISTS vt_result;
            FIN;

    protected array $all;
    protected array $open;
    protected array $not_open;


    protected function checkParamErrors($params): false|array {
        return false;
    }

    protected function reportColumns(): array {

        $totals_format = [CellFormat::GeneralTotal, CellFormat::GeneralSubtotal];
        $totals_all = [CellFormat::CountAllTotal, CellFormat::CountAllSubtotal];
        $totals_open = [CellFormat::CountOpenTotal, CellFormat::CountOpenSubtotal];
        $totals_not_open = [CellFormat::CountNotOpenTotal, CellFormat::CountNotOpenSubtotal];
        $params = Bot::$session->get('data');
        
        $columns = [
            new Column('№ пп', 0, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format),
            new Column('Ответственный', 20, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, false, null, $params['groupby'][0] == 'responsible'?1:null),
            new Column('Категория', 17, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, false, null, $params['groupby'][0] == 'category'?1:null),
            new Column('ОМСУ', 12, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, false, null, $params['groupby'][0] == 'omsu'?1:null),
            new Column('Год', 9, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format, false, null, $params['groupby'][0] == 'year'?1:null),
            new Column('Наименование объекта', 20, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_all),
            new Column('Фактическая дата открытия', 13, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_open),
            new Column('Плановая дата открытия', 13, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_not_open),
            new Column('Степень готовности %', 10, CellFormat::GeneralTH, CellFormat::PercentTD, $totals_format),
        ];
        
        $columns[5]->setTotalsCollector(new TotalCount([$columns[5]]));
        $columns[6]->setTotalsCollector(new TotalCount([$columns[6]]));
        $columns[7]->setTotalsCollector(new TotalNulls([$columns[5], $columns[6]]));
        
        return $columns;
    }

    protected function reportData($params): array {
        $sql = static::SQL_QUERY;
        
        foreach ($params as $key => $value) {
            if ($key != 'cat2') {
                $sql = str_replace("%$key%", implode(',', $value), $sql);
            } else {
                $cats = []; 
                $param_descr = $this->getParams()[1];
                foreach ($value as $v) {
                    $cat = $param_descr->valueByValue($v);
                    $cats[] = $cat->getTitle();
                }
                $implode = implode("','", $cats);
                $in = "'$implode'";
                $sql = str_replace("%$key%", $in, $sql);
            }
        }
        $group_order = str_replace('#', ' ', $params['sortby'][0]);
        if ($params['groupby'][0] != 'none') {
            $group_order = $params['groupby'][0]. ', '. $group_order;
        }
        $sql = str_replace('%group_order%', $group_order, $sql);
        $sql = str_replace('%risky_only%', (string)$params['risky_only'][0], $sql);
        
        $sth = DB::prepare($sql);
        $sth->execute();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();

        $result = $sth->fetchAll(\PDO::FETCH_NUM);
        $this->all = [];
        $this->open = [];
        $this->not_open = [];
        
        foreach ($result as $row) {
            $responsible = $row[1];
            $open_date_fact = $row[6];
            
            isset($this->all['total']) ? $this->all['total']++ : $this->all['total'] = 1;
            isset($this->all[$responsible]) ? $this->all[$responsible]++ : $this->all[$responsible] = 1;
            if ($open_date_fact) {
                isset($this->open['total']) ? $this->open['total']++ : $this->open['total'] = 1;
                isset($this->open[$responsible]) ? $this->open[$responsible]++ : $this->open[$responsible] = 1;
            } else {
                isset($this->not_open['total']) ? $this->not_open['total']++ : $this->not_open['total'] = 1;
                isset($this->not_open[$responsible]) ? $this->not_open[$responsible]++ : $this->not_open[$responsible] = 1;
            }
        }
        return $result;
    }

    protected function reportSummary($params): \losthost\BlagoBot\service\ReportSummary {
        
        $totals = [
            ['title' => 'Всего объектов', 'value' => $this->all['total']],
            ['title' => 'Открыто объектов', 'value' => $this->open['total']],
            ['title' => 'Не открыто объектов', 'value' => $this->not_open['total']],
            null,
            ['title' => 'В том числе по ответственным', 'value' => ''],
        ];
        
        unset($this->all['total']);
        unset($this->open['total']);
        unset($this->not_open['total']);
        
        ksort($this->all);
        ksort($this->open);
        ksort($this->not_open);
        
        foreach ($this->all as $key => $value) {
            $totals[] = null;
            $totals[] = ['title' => $key, 'value' => ''];
            $totals[] = ['title' => 'Всего объектов', 'value' => $value];
            $totals[] = ['title' => 'Открыто объектов', 'value' => empty($this->open[$key])?0:$this->open[$key]];
            $totals[] = ['title' => 'Не открыто объектов', 'value' => empty($this->not_open[$key])?0:$this->not_open[$key]];
            
        }
        
        return new ReportSummary('Степень готовности объектов', date_create_immutable(), $totals);
    }

    protected function resultType(): int|string {
        return static::RESULT_TYPE_XLSX;
    }
    
    protected function initParams() {
        $this->params = [
            new ParamDescriptionPeriod($this),
            new ParamDescriptionCategory2All($this),
            new ParamDescriptionResponsibleAll($this),
            new ParamDescriptionOmsuAll($this),
            new ParamDescriptionRiskyOnly($this),
            new ParamDescriptionReadyGroupBy($this),
            new ParamDescriptionReadySortBy($this),
        ];
    }
    
    public function getCustomResultViewClass(): ?string {
        return \losthost\BlagoBot\view\ReportReadyView::class;
    }
}
