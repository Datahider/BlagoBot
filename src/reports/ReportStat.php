<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\BlagoBot\service\xls\Column;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\DB\DB;
use losthost\BlagoBot\service\ReportSummary;

class ReportStat extends AbstractReport {

    protected function checkParamErrors($params): false|array {
        return false;
    }

    protected function initParams() {
        $this->params = null;
    }

    protected function reportColumns(): array {
        
        $totals_format = [CellFormat::GeneralTotal, CellFormat::GeneralSubtotal];
        
        return [
            //new Column('№ п/п', 7, CellFormat::GeneralTH, CellFormat::NumberingTD),
            new Column('ФИО', 25, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format),
            //new Column('Telegram', 20, CellFormat::GeneralTH, CellFormat::GeneralTD),
            new Column('Всего запросов', 15, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
            new Column('На этой неделе', 30, CellFormat::GeneralTH, CellFormat::GeneralTD, $totals_format, true),
        ];
    }

    protected function reportData($params): array {
        $sth = DB::prepare($this->getSqlQuery());
        $sth->execute([date_create('this week')->format('Y-m-d')]);
        
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

                    COUNT(logrep.id) AS total,
                    SUM(
                            CASE
                                    WHEN logrep.time_start < ? THEN 0
                                    ELSE 1
                            END
                    ) AS this_week
            FROM 
                    blago_log_report AS logrep
                    LEFT JOIN blago_user AS user ON user.id = logrep.user_id
                    LEFT JOIN blago_telle_users AS tguser ON tguser.id = user.tg_user
            GROUP BY
                    logrep.user_id  
            ORDER BY this_week DESC, total DESC            
        FIN;
    }
}
