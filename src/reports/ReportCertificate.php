<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\DB\DB;
use losthost\BlagoBot\service\xls\Column;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\BlagoBot\data\report_param_value;
use losthost\BlagoBot\service\ReportSummary;
use losthost\BlagoBot\data\x_omsu;
use losthost\DB\DBList;
use losthost\BlagoBot\service\TotalPercentage;
use losthost\BlagoBot\service\TotalTrickyPercentage;

/**
 * Отчет справки по годам
 *
 * @author web
 */
class ReportCertificate extends AbstractReport {
    
    const YEARS_START   = 2019;
    const YEARS_END     = 2027;
    const YEARS_HEADERS = [
        2019 => 'Оплата 2019',
        2020 => 'Оплата 2020',
        2021 => 'Оплата 2021',
        2022 => 'Оплата 2022',
        2023 => 'Оплата 2023',
        2024 => 'Лимит 2024',
        2025 => 'Лимит 2025',
        2026 => 'Лимит 2026',
        2027 => 'Лимит 2027',
    ];
    
    protected array $years;
    protected array $omsu_names;

    protected function initParams() {
        $this->params = [
            new \losthost\BlagoBot\params\ParamDescriptionOmsu($this),
            new \losthost\BlagoBot\params\ParamDescriptionYearFull($this)
        ];
    }

    protected function checkParamErrors($params): false|array {
        $this->years = [];
        foreach ($params['certyears'] as $id) {
            $year = new report_param_value(['id' => $id]);
            $this->years[] = (int)$year->value;
        }
        
        $this->omsu_names = [];
        foreach ($params['muni'] as $id) {
            $omsu = new x_omsu(['id' => $id]);
            $this->omsu_names[] = $omsu->name;
        }

        return false;
    }

    protected function reportColumns(): array {
        $ftd = CellFormat::GeneralTD;
        $fsd = CellFormat::SummTD;
        
        $totals_format = [CellFormat::GeneralTotal, CellFormat::GeneralSubtotal];
        $columns = [
            new Column('№ пп', 0, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format),
            new Column('ОМСУ', 15, CellFormat::GeneralTH, $ftd, $totals_format, false, null, 1),
            new Column('Наименование объекта', 50, CellFormat::GeneralTH, $ftd, $totals_format),
            new Column('Тип объекта', 20, CellFormat::GeneralTH, $ftd, $totals_format),
            new Column('Кол-во', 5, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format, true),
            new Column('Годы реализации', 10, CellFormat::GeneralTH, CellFormat::NumberingTD, $totals_format),
            new Column('Всего', 15, CellFormat::GeneralTH, $fsd, $totals_format, true),
        ];
        
        foreach (self::YEARS_HEADERS as $year => $header) {
            if (array_search($year, $this->years) !== false) {
                $columns[] = new Column($header, 15, CellFormat::GeneralTH, $fsd, $totals_format, true);
            }
        }
        
        return $columns;
    }

    protected function reportData($params): array {
        
        $sql = $this->getSqlQuery($this->omsu_names, $this->years);
        $sth = DB::prepare($sql);
        $sth->execute();
        error_log($sth->queryString);
        
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        echo $sth->nextRowset();
        $data = $sth->fetchAll(\PDO::FETCH_NUM);
        
        foreach ($data as $key => $row) {
            foreach (range(15, 7) as $index) {
                $year = $index+2019-7;
                if ($row[$index] !== null && $row[$index] > 0) {
                    $data[$key][5] = $data[$key][5] ? ("$year,{$data[$key][5]}") : $year; 
                }
                if (array_search($year, $this->years) === false) {
                    array_splice($data[$key], $index, 1);
                }
            }
            $data[$key][5] = $this->formatYears($data[$key][5]);
        }
        
        return $data;
    }

    protected function formatYears($text) {
        $years_array = explode(",", $text);
        
        if (count($years_array) < 3) {
            return $text;
        }
        
        $remove = [];
        foreach (range(1, count($years_array)-2) as $index) {
            if ($years_array[$index-1]+1 == $years_array[$index] && $years_array[$index+1]-1 == $years_array[$index]) {
                $remove[] = $index;
            }
        }
        
        if (count($remove)) {
            foreach (range(count($remove)-1, 0) as $index) {
                
            }
        }

        $result = "";
        $minus = "-";
        foreach (range(0, count($years_array)-1) as $index) {
            if ($index === 0) {
                $result = $years_array[0];
            } elseif (array_search($index, $remove) !== false) {
                $result .= $minus;
                $minus = "";
            } elseif ($minus == "") {
                $result .= $years_array[$index];
                $minus = "-";
            } else {
                $result .= ", $years_array[$index]";
            }
        }
        
        return $result;
    }
    
    protected function reportSummary($params): \losthost\BlagoBot\service\ReportSummary {

        return new ReportSummary(
                'Информация о финансировании по ГП "Формирование современной комфортной городской среды"', 
                date_create_immutable(), 
                [
                    ['title' => 'ОМСУ', 'value' => implode(', ', $this->omsu_names)],
                    ['title' => 'Годы', 'value' => implode(', ', $this->years)],
                ]
                );
        
    }

    protected function resultType(): int {
        return self::RESULT_TYPE_XLSX;
    }
    
    protected function getSqlQuery(array $omsu_names, array $years) : string {
        $sql = <<<FIN
            DROP TEMPORARY TABLE IF EXISTS vt_old_base;
            DROP TEMPORARY TABLE IF EXISTS vt_current_base;
            DROP TEMPORARY TABLE IF EXISTS vt_full_base;
            DROP TEMPORARY TABLE IF EXISTS vt_current_years;
            DROP TEMPORARY TABLE IF EXISTS vt_result;

            CREATE TEMPORARY TABLE vt_current_years 
            SELECT 2024 AS year
            UNION ALL
            SELECT 2025
            UNION ALL
            SELECT 2026
            UNION ALL
            SELECT 2027;

            CREATE TEMPORARY TABLE vt_old_base SELECT
                    omsu_name,
                    object_name,
                    category2_name,
                    year,
                    SUM(object_count) AS object_count,
                    SUM(payment_total) AS payment_total
            FROM 
                    [x_prev]
            WHERE 
                    object_name <> "Исключено"
            GROUP BY 
                    omsu_name, 
                    object_name,
                    category2_name,
                    year
            ;

            CREATE TEMPORARY TABLE vt_current_base SELECT
                    omsus.name AS omsu_name,
                    object.name AS object_name,
                    object.category2_name AS category2_name,
                    years.year AS year, 
                    1 AS object_count,

                    IFNULL(SUM(fb.value), 0) + IFNULL(SUM(bm.value), 0) + IFNULL(SUM(bmo.value), 0) + IFNULL(SUM(omsu.value), 0) AS limit_total

            FROM
                    [x_object] AS object
                    LEFT JOIN [x_omsu] AS omsus ON omsus.id = object.omsu_id 
                    LEFT JOIN vt_current_years AS years ON 1
                    LEFT JOIN [x_year_data] AS fb ON object.id = fb.x_object_id AND fb.type = "Лимит ФБ" AND fb.year = years.year
                    LEFT JOIN [x_year_data] AS bm ON object.id = bm.x_object_id AND bm.type = "Лимит БМ" AND bm.year = years.year
                    LEFT JOIN [x_year_data] AS bmo ON object.id = bmo.x_object_id AND bmo.type = "Лимит БМО" AND bmo.year = years.year
                    LEFT JOIN [x_year_data] AS omsu ON object.id = omsu.x_object_id AND omsu.type = "Лимит ОМСУ" AND omsu.year = years.year
            GROUP BY
                    omsu_name,
                    object_name,
                    category2_name,
                    year
            HAVING 
                    limit_total <> 0;

            CREATE TEMPORARY TABLE vt_full_base SELECT
                    base.omsu_name,
                    base.object_name,
                    base.category2_name,
                    SUM(CASE
                        WHEN base.year IN ({:years}) THEN base.object_count
                        ELSE 0
                    END) AS object_count, 
                    SUM(CASE
                        WHEN base.year IN ({:years}) THEN base.payment_total
                        ELSE 0
                    END) AS payment_total, 
                    IFNULL(SUM(base2019.payment_total), 0) AS payment_2019, 
                    IFNULL(SUM(base2020.payment_total), 0) AS payment_2020, 
                    IFNULL(SUM(base2021.payment_total), 0) AS payment_2021, 
                    IFNULL(SUM(base2022.payment_total), 0) AS payment_2022, 
                    IFNULL(SUM(base2023.payment_total), 0) AS payment_2023,
                    0 AS payment_2024,
                    0 AS payment_2025,
                    0 AS payment_2026,
                    0 AS payment_2027 
            FROM vt_old_base AS base
                    LEFT JOIN vt_old_base AS base2019 ON base.omsu_name = base2019.omsu_name AND base.object_name = base2019.object_name AND base.category2_name = base2019.category2_name AND base.year = base2019.year AND base2019.year = 2019
                    LEFT JOIN vt_old_base AS base2020 ON base.omsu_name = base2020.omsu_name AND base.object_name = base2020.object_name AND base.category2_name = base2020.category2_name AND base.year = base2020.year AND base2020.year = 2020
                    LEFT JOIN vt_old_base AS base2021 ON base.omsu_name = base2021.omsu_name AND base.object_name = base2021.object_name AND base.category2_name = base2021.category2_name AND base.year = base2021.year AND base2021.year = 2021
                    LEFT JOIN vt_old_base AS base2022 ON base.omsu_name = base2022.omsu_name AND base.object_name = base2022.object_name AND base.category2_name = base2022.category2_name AND base.year = base2022.year AND base2022.year = 2022
                    LEFT JOIN vt_old_base AS base2023 ON base.omsu_name = base2023.omsu_name AND base.object_name = base2023.object_name AND base.category2_name = base2023.category2_name AND base.year = base2023.year AND base2023.year = 2023
            WHERE 
                    base.omsu_name IN ({:omsu_names}) 
            GROUP BY
                    base.omsu_name, base.object_name, base.category2_name
            UNION ALL
            SELECT 
                    base.omsu_name,
                    base.object_name,
                    base.category2_name,
                    MAX(CASE
                        WHEN base.year IN ({:years}) THEN base.object_count
                        ELSE 0
                    END),
                    SUM(CASE
                        WHEN base.year IN ({:years}) THEN base.limit_total
                        ELSE 0
                    END), 
                    0 AS payment_2019,
                    0 AS payment_2020,
                    0 AS payment_2021,
                    0 AS payment_2022,
                    0 AS payment_2023,
                    SUM(base2024.limit_total), 
                    SUM(base2025.limit_total), 
                    SUM(base2026.limit_total), 
                    SUM(base2027.limit_total)
            FROM vt_current_base AS base
                    LEFT JOIN vt_current_base AS base2024 ON base.omsu_name = base2024.omsu_name AND base.object_name = base2024.object_name AND base.category2_name = base2024.category2_name AND base.year = base2024.year AND base2024.year = 2024
                    LEFT JOIN vt_current_base AS base2025 ON base.omsu_name = base2025.omsu_name AND base.object_name = base2025.object_name AND base.category2_name = base2025.category2_name AND base.year = base2025.year AND base2025.year = 2025
                    LEFT JOIN vt_current_base AS base2026 ON base.omsu_name = base2026.omsu_name AND base.object_name = base2026.object_name AND base.category2_name = base2026.category2_name AND base.year = base2026.year AND base2026.year = 2026
                    LEFT JOIN vt_current_base AS base2027 ON base.omsu_name = base2027.omsu_name AND base.object_name = base2027.object_name AND base.category2_name = base2027.category2_name AND base.year = base2027.year AND base2027.year = 2027
            WHERE 
                    base.omsu_name IN ({:omsu_names}) 
            GROUP BY
                    base.omsu_name, base.object_name, base.category2_name
            ;

            CREATE TEMPORARY TABLE vt_result SELECT 
                    omsu_name,
                    object_name,
                    category2_name,
                    SUM(object_count) AS object_count,
                    "" AS years,
                    SUM(payment_total)/1000 AS payment_total, 
                    SUM(payment_2019)/1000 AS payment_2019,
                    SUM(payment_2020)/1000 AS payment_2020,
                    SUM(payment_2021)/1000 AS payment_2021,
                    SUM(payment_2022)/1000 AS payment_2022,
                    SUM(payment_2023)/1000 AS payment_2023,
                    SUM(payment_2024)/1000 AS payment_2024,
                    SUM(payment_2025)/1000 AS payment_2025,
                    SUM(payment_2026)/1000 AS payment_2026,
                    SUM(payment_2027)/1000 AS payment_2027
            FROM 
                    vt_full_base
            GROUP BY
                    omsu_name,
                    object_name,
                    category2_name	
            ORDER BY
                    omsu_name, 
                    CASE WHEN SUM(payment_2027) > 0 THEN 2027 ELSE
                    CASE WHEN SUM(payment_2026) > 0 THEN 2026 ELSE
                    CASE WHEN SUM(payment_2025) > 0 THEN 2025 ELSE
                    CASE WHEN SUM(payment_2024) > 0 THEN 2024 ELSE
                    CASE WHEN SUM(payment_2023) > 0 THEN 2023 ELSE
                    CASE WHEN SUM(payment_2022) > 0 THEN 2022 ELSE
                    CASE WHEN SUM(payment_2021) > 0 THEN 2021 ELSE
                    CASE WHEN SUM(payment_2020) > 0 THEN 2020 ELSE
                    CASE WHEN SUM(payment_2019) > 0 THEN 2019 ELSE 0
                    END END END END END END END END END,
                    category2_name, object_name;

            SET @row_number = 0;
        
            SELECT 
                (@row_number:=@row_number + 1) AS num,
                result.*
            FROM vt_result AS result
            WHERE 
                payment_total > 0;
   
            FIN;
    
        $sql = str_replace('{:omsu_names}', "'". implode("','", $omsu_names). "'", $sql);
        $sql = str_replace('{:years}', implode(",", $years), $sql);
        
        return $sql;
    }
}
