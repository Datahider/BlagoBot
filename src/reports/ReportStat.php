<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\BlagoBot\service\xls\Column;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\DB\DB;
use losthost\BlagoBot\service\ReportSummary;
use losthost\BlagoBot\data\user;

class ReportStat extends AbstractReport {

    protected function checkParamErrors($params): false|array {
        return false;
    }

    protected function initParams() {
        $this->params = null;
    }
    
    #[\Override]
    protected function checkAccessRights(): bool {
        global $b_user;
        
        if ($b_user->access_level <> user::AL_ADMIN) {
            return false;
        }
        return true;
    }

    protected function reportColumns(): array {
        
        $totals_format = [CellFormat::GeneralTotal, CellFormat::GeneralSubtotal];
        
        return [
            //new Column('№ п/п', 7, CellFormat::GeneralTH, CellFormat::NumberingTD),
            new Column('ФИО', 25, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format),
            //new Column('Telegram', 20, CellFormat::GeneralTH, CellFormat::GeneralTD),
            new Column('Всего отчетов', 15, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
            new Column('Отчетов на этой неделе', 15, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
            new Column('Всего ИИ', 15, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
            new Column('ИИ на этой неделе', 15, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
            new Column('Всего веб-поиск', 15, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
            new Column('Веб-поиск на этой неделе', 15, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
        ];
    }

    protected function reportData($params): array {
        $sth = DB::prepare($this->getSqlQuery());
        $sth->execute(['week_start' => date_create('this week')->format('Y-m-d')]);
        
        $data = $sth->fetchAll(\PDO::FETCH_NUM);        
        
        return $data;
    }

    protected function reportSummary($params): ReportSummary {
        return new ReportSummary('Статистика использования бота', date_create_immutable(), []);
    }

    protected function resultType(): int {
        return static::RESULT_TYPE_XLSX;
    }
    
    protected function getSqlQuery() {
        
        return <<<FIN
            SELECT
                    CASE 
                            WHEN CONCAT(IFNULL(user.surname, ""), " ", IFNULL(user.name, ""), " ", IFNULL(user.fathers_name, "")) <> "  " 
                                    THEN CONCAT(IFNULL(user.surname, ""), " ", IFNULL(user.name, ""), " ", IFNULL(user.fathers_name, ""))
                            WHEN tguser.username IS NULL OR tguser.username = ""
                                    THEN CONCAT(tguser.first_name, " ", IFNULL(tguser.last_name, ""), " (tg_id=", tguser.id, ")")
                            ELSE
                                    CONCAT(tguser.first_name, " ", IFNULL(tguser.last_name, ""), " (@", tguser.username, ")")
                    END AS FIO,

                    SUM(
                        CASE 
                            WHEN report_class <> 'losthost\\\\BlagoBot\\\\service\\\\AIGateway' AND report_class <> 'losthost\\\\BlagoBot\\\\service\\\\AIFunctionSearch' THEN 1
                            ELSE 0
                        END
                    ) AS reports_total,
                    SUM(
                        CASE
                            WHEN logrep.time_start < :week_start THEN 0
                            WHEN report_class <> 'losthost\\\\BlagoBot\\\\service\\\\AIGateway' AND report_class <> 'losthost\\\\BlagoBot\\\\service\\\\AIFunctionSearch' THEN 1
                            ELSE 0
                        END
                    ) AS reports_this_week,
                    SUM(
                        CASE 
                            WHEN report_class = 'losthost\\\\BlagoBot\\\\service\\\\AIGateway' THEN 1
                            ELSE 0
                        END
                    ) AS ai_total,
                    SUM(
                        CASE
                            WHEN logrep.time_start < :week_start THEN 0
                            WHEN report_class = 'losthost\\\\BlagoBot\\\\service\\\\AIGateway' THEN 1
                            ELSE 0
                        END
                    ) AS ai_this_week,
                    SUM(
                        CASE 
                            WHEN report_class = 'losthost\\\\BlagoBot\\\\service\\\\AIFunctionSearch' THEN 1
                            ELSE 0
                        END
                    ) AS search_total,
                    SUM(
                        CASE
                            WHEN logrep.time_start < :week_start THEN 0
                            WHEN report_class = 'losthost\\\\BlagoBot\\\\service\\\\AIFunctionSearch' THEN 1
                            ELSE 0
                        END
                    ) AS search_this_week
            FROM 
                    blago_log_report AS logrep
                    INNER JOIN blago_user AS user ON user.id = logrep.user_id
                    LEFT JOIN blago_telle_users AS tguser ON tguser.id = user.tg_user
            GROUP BY
                    logrep.user_id  
            ORDER BY reports_this_week DESC, reports_total DESC            
        FIN;
    }
}
