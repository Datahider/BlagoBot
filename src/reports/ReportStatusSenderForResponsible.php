<?php

namespace losthost\BlagoBot\reports;

use losthost\DB\DBView;
use losthost\DB\DB;
use losthost\BlagoBot\service\ReportSummary;

use function losthost\BlagoBot\__;
use function \losthost\BlagoBot\sendSplitMessage;

class ReportStatusSenderForResponsible extends ReportStatusSender {
    
    const SQL_QUERY_RESPONSIBLE = <<<FIN
            SELECT 
                responsible.id AS responsible_id,
                user.id as user_id,
                user.surname as user_surname,
                user.name as user_name,
                user.fathers_name as user_fathers_name,
                user.tg_user as user_tg_id,

                COUNT(DISTINCT object.uin) as total_objects,
                SUM(year_data.value) / 1000 as total_limit

            FROM 
                [x_responsible] as responsible
                LEFT JOIN [user] as user ON responsible.user_id = user.id
                LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                LEFT JOIN [x_year_data] as year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")

            WHERE 
                %where%
                AND year_data.value IS NOT NULL

            GROUP BY 
                responsible.id, responsible.user_id, user_surname, user_name, user_fathers_name, user_tg_id
            FIN;
    
    const SQL_QUERY_OBJECTS_DELAYED = <<<FIN
                SELECT 
                    responsible.id as responsible_id,
                    1 as delay_type,
                    object.name as object_name,
                    object.moge_in_plan as date_planned
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                WHERE 
                    %where%
                    AND object.moge_in_plan IS NOT NULL 
                    AND object.moge_in_plan < :current_date
                    AND object.moge_in_fact IS NULL

                UNION ALL

                SELECT 
                    responsible.id,
                    2,
                    object.name,
                    object.moge_out_plan
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                WHERE
                    %where%
                    AND object.moge_out_plan IS NOT NULL 
                    AND object.moge_out_plan < :current_date
                    AND object.moge_out_fact IS NULL

                UNION ALL

                SELECT 
                    responsible.id,
                    3,
                    object.name,
                    object.rgmin_in_plan
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                WHERE 
                    %where%
                    AND object.rgmin_in_plan IS NOT NULL 
                    AND object.rgmin_in_plan < :current_date
                    AND object.purchase_level = 2
                    AND object.rgmin_in_fact IS NULL

                UNION ALL

                SELECT 
                    responsible.id,
                    4,
                    object.name,
                    object.rgmin_in_plan
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                WHERE 
                    %where%
                    AND object.rgmin_in_plan IS NOT NULL 
                    AND object.rgmin_in_plan < :current_date
                    AND object.purchase_level = 1
                    AND object.rgmin_in_fact IS NULL

                UNION ALL

                SELECT 
                    responsible.id,
                    5,
                    object.name,
                    object.psmr_plan
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                WHERE 
                    %where%
                    AND object.psmr_plan IS NOT NULL 
                    AND object.psmr_plan < :current_date
                    AND object.psmr_fact IS NULL

                UNION ALL

                SELECT 
                    responsible.id,
                    6,
                    object.name,
                    object.ksmr_plan
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                WHERE 
                    %where%
                    AND object.ksmr_plan IS NOT NULL 
                    AND object.ksmr_plan < :current_date
                    AND object.ksmr_fact IS NULL

                UNION ALL

                SELECT 
                    responsible.id,
                    7,
                    object.name,
                    object.open_date_planned
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                WHERE 
                    %where%
                    AND object.open_date_planned IS NOT NULL 
                    AND object.open_date_planned < :current_date
                    AND object.open_date_fact IS NULL

                FIN;

    const SQL_QUERY_OBJECTS = <<<FIN
                SELECT 
                    responsible.id AS responsible_id,
                    object.name AS object_name,
                    object.moge_in_plan AS moge_in_plan,
                    object.moge_in_fact AS moge_in_fact,
                    object.moge_out_plan AS moge_out_plan,
                    object.moge_out_fact AS moge_out_fact,
                    object.rgmin_in_plan AS rgmin_in_plan,
                    object.purchase_level AS purchase_level,
                    object.rgmin_in_fact AS rgmin_in_fact,
                    object.psmr_plan AS psmr_plan,
                    object.psmr_fact AS psmr_fact,
                    object.ksmr_plan AS ksmr_plan,
                    object.ksmr_fact AS ksmr_fact,
                    object.open_date_planned AS open_date_planned,
                    object.open_date_fact AS open_date_fact,
                    SUM(year_data.value) / 1000 AS total_limit
                FROM
                    [x_responsible] as responsible
                    LEFT JOIN [x_object] as object ON responsible.id = object.x_responsible_id
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    %where%
                    AND year_data.value IS NOT NULL
                GROUP BY 
                    object.id
                FIN;    

    protected function initParams() {
        $this->params = [
            new \losthost\BlagoBot\params\ParamDescriptionResponsible($this),
            new \losthost\BlagoBot\params\ParamDescriptionMessageType($this),
            new \losthost\BlagoBot\params\ParamDescriptionSelfCopy($this)
        ];
    }

    protected function getWhere($params) {
        return isset($params['responsible']) ? 'responsible.id IN ('. implode(',', $params['responsible']). ')' : '1';
    }
    
    protected function getCommonStatus($params) {
        $replace = [
            'where' => $this->getWhere($params),
            'current_year' => $this->getCurrentYear(),
        ];
        
        $sql_responsible = $this->replaceVars(static::SQL_QUERY_RESPONSIBLE, $replace);
        $sql_objects = $this->replaceVars(static::SQL_QUERY_OBJECTS, $replace);

        $responsible_view = new DBView($sql_responsible);
        
        $current_date = date_create_immutable()->format(DB::DATE_FORMAT);
        $current_year = $this->getCurrentYear();
        
        $objects_view = new DBView($sql_objects);
        
        while ($responsible_view->next()) {
            $responsible_data[$responsible_view->responsible_id] = [
                'responsible_id' => $responsible_view->responsible_id,
                'user_id' => $responsible_view->user_id,
                'user_surname' => $responsible_view->user_surname,
                'user_name' => $responsible_view->user_name,
                'user_fathers_name' => $responsible_view->user_fathers_name,
                'user_tg_id' => $responsible_view->user_tg_id,
                'total_objects' => $responsible_view->total_objects,
                'total_limit' => $responsible_view->total_limit,
                'current_year' => $current_year,
                'current_date' => $current_date,
                'object_data' => []
            ];
            
        }
        
        while ($objects_view->next()) {
            $responsible_data[$objects_view->responsible_id]['object_data'][] = [
                'object_name' => $objects_view->object_name,
                'moge_in_plan' => $objects_view->moge_in_plan,
                'moge_in_fact' => $objects_view->moge_in_fact,
                'moge_out_plan' => $objects_view->moge_out_plan,
                'moge_out_fact' => $objects_view->moge_out_fact,
                'rgmin_in_plan' => $objects_view->rgmin_in_plan,
                'purchase_level' => $objects_view->purchase_level,
                'rgmin_in_fact' => $objects_view->rgmin_in_fact,
                'psmr_plan' => $objects_view->psmr_plan,
                'psmr_fact' => $objects_view->psmr_fact,
                'ksmr_plan' => $objects_view->ksmr_plan,
                'ksmr_fact' => $objects_view->ksmr_fact,
                'open_date_planned' => $objects_view->open_date_planned,
                'open_date_fact' => $objects_view->open_date_fact,
                'total_limit' => $objects_view->total_limit,
            ];
        }
        return $responsible_data;
        
    }

    protected function getDelays($params) {
        $replace = [
            'where' => $this->getWhere($params),
            'current_year' => $this->getCurrentYear(),
        ];
        $sql_responsible = $this->replaceVars(static::SQL_QUERY_RESPONSIBLE, $replace);
        $sql_objects = $this->replaceVars(static::SQL_QUERY_OBJECTS_DELAYED, $replace);
        
        $responsible_view = new DBView($sql_responsible);
        $current_date = date_create_immutable()->format(DB::DATE_FORMAT);
        $current_year = $this->getCurrentYear();
        $objects_view = new DBView($sql_objects, ['current_date' => $current_date]);
        
        while ($responsible_view->next()) {
            $responsible_data[$responsible_view->responsible_id] = [
                'responsible_id' => $responsible_view->responsible_id,
                'user_id' => $responsible_view->user_id,
                'user_surname' => $responsible_view->user_surname,
                'user_name' => $responsible_view->user_name,
                'user_fathers_name' => $responsible_view->user_fathers_name,
                'user_tg_id' => $responsible_view->user_tg_id,
                'total_objects' => $responsible_view->total_objects,
                'total_limit' => $responsible_view->total_limit,
                1 => [],
                2 => [],
                3 => [],
                4 => [],
                5 => [],
                6 => [],
                7 => [],
                'total_delays' => 0,
                'current_year' => $current_year,
                'current_date' => $current_date
            ];
        }

        while ($objects_view->next()) {
            $responsible_data[$objects_view->responsible_id][$objects_view->delay_type][] = [
                'object_name' => $objects_view->object_name,
                'date_planned' => $objects_view->date_planned
            ];
            $responsible_data[$objects_view->responsible_id]['total_delays']++;
        }
    
        return $responsible_data;
    }
    
    protected function getCommonTemplateName() : string {
        return 'tpl_common_status_responsible.php';
    }
    
    protected function getDelayTemplateName() : string {
        return 'tpl_delays_responsible.php';
    }

    protected function reportSummary($params): ReportSummary {
        if ($params['msgtype'][0] == 89) {
            $params['stat'] = [
                'Обработано кураторов' => $this->total,
                'Отсутствуют риски' => $this->safe,
                'Выявлены риски' => $this->risky,
                'Направлено сообщений' => $this->sent,
                'Ошибки отправки' => $this->errors
            ];
        }
        return new ReportSummary('Отправка СМС о статусе', date_create_immutable(), $params);
    }
    
    protected function fillSendResult($data, $send_result, $msg_text) : array {
        if ($data['user_tg_id']) {
            $recipient = "$data[user_surname] $data[user_name] $data[user_fathers_name]";
        } else {
            $recipient = '--НЕ ЗАДАН--';
        }
        return [
                $recipient,
                $send_result,
                '',
                $msg_text,
            ];
    }

    protected function reportColumns(): array {
        return [__('Адресат'), __('Статус')];
    }

}
