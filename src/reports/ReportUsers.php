<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\DB\DB;
use losthost\BlagoBot\service\xls\Column;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\BlagoBot\service\ReportSummary;

class ReportUsers extends AbstractReport {
    
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

    protected function checkParamErrors($params): false|array {
        return false;
    }

    protected function reportColumns(): array {
        return [
            new Column('№ п/п', 7, CellFormat::GeneralTH, CellFormat::NumberingTD),
            new Column('Фамилия', 15, CellFormat::GeneralTH, CellFormat::GeneralTD),
            new Column('Имя', 15, CellFormat::GeneralTH, CellFormat::GeneralTD),
            new Column('Отчество', 15, CellFormat::GeneralTH, CellFormat::GeneralTD),
            new Column('ID', 7, CellFormat::GeneralTH, CellFormat::NumberingTD),
            new Column('Telegram', 20, CellFormat::GeneralTH, CellFormat::GeneralTD),
            new Column('Роль в боте', 15, CellFormat::GeneralTH, CellFormat::GeneralTD),
            new Column('Привязка к ОМСУ', 30, CellFormat::GeneralTH, CellFormat::GeneralTD),
        ];
    }

    protected function reportData($params): array {
        
        $sth = DB::prepare($this->getSqlQuery());
        $sth->execute();
        
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        
        $data = $sth->fetchAll(\PDO::FETCH_NUM);        
        
        foreach ($data as $key=>$row) {
            $data[$key][] = $this->getUserBindings($row[4]);
        }
        
        return $data;
    }

    protected function getUserBindings(int $user_id) {
        $sth = DB::prepare(<<<FIN
            SELECT
                    CONCAT(omsu.name, ' (глава)') AS binding 
            FROM 
                    [x_omsu] AS omsu
            WHERE
                    omsu.head_id = ?

            UNION ALL

            SELECT
                    CONCAT(omsu.name, ' (замглавы)') AS binding 
            FROM 
                    [x_omsu] AS omsu
            WHERE
                    omsu.vicehead_id = ?
            
            FIN);
        
        $sth->execute([$user_id, $user_id]);
        
        $result = [];
        while ($column = $sth->fetchColumn()) {
            $result[] = $column;
        }
        
        return implode(', ', $result);
    }
    
    protected function getSqlQuery() {
        return <<<FIN
            DROP TEMPORARY TABLE if EXISTS vt_result;

            CREATE TEMPORARY TABLE vt_result SELECT 
                    CASE
                            WHEN user.surname IS NULL THEN tguser.last_name  
                            ELSE user.surname
                    END AS surname,
                    CASE
                            WHEN user.name IS NULL THEN tguser.first_name  
                            ELSE user.name
                    END AS name,
                    CASE
                            WHEN user.fathers_name IS NULL THEN '!!!️'   
                            ELSE user.fathers_name
                    END AS fathers_name,
                    user.id AS id,
                    CONCAT('@', tguser.username) AS username,
                    CASE 
                            WHEN user.access_level = 'user' THEN 'пользователь'
                            WHEN user.access_level = 'admin' THEN 'админ'
                            WHEN user.access_level = 'restricted' THEN 'получатель рассылки'
                            ELSE 'не известно'
                    END AS access_level
            FROM 
                    [user] AS user
                    LEFT JOIN [telle_users] AS tguser ON tguser.id = user.tg_user
            ORDER BY 
                    surname,
                    name,
                    fathers_name;

            SET @row_number = 0;

            SELECT 
                (@row_number:=@row_number + 1) AS num,
                    vt_result.*
            FROM 
                    vt_result;

            FIN;
    }
    protected function reportSummary($params): \losthost\BlagoBot\service\ReportSummary {
        return new ReportSummary('Список пользователей бота', date_create_immutable(), []);
    }

    protected function resultType(): int {
        return static::RESULT_TYPE_XLSX;
    }
}
