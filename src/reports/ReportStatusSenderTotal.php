<?php

namespace losthost\BlagoBot\reports;

use losthost\telle\Bot;

class ReportStatusSenderTotal extends ReportStatusSenderForResponsible {
    
    const SQL_QUERY_RESPONSIBLE = <<<FIN
            SELECT 
                0 as responsible_id,
                user.id as user_id,
                user.surname as user_surname,
                user.name as user_name,
                user.fathers_name as user_fathers_name,
                user.tg_user as user_tg_id,

                COUNT(DISTINCT object.uin) as total_objects,
                SUM(year_data.value) / 1000 as total_limit

            FROM 
                [user] as user
                LEFT JOIN [x_object] as object ON 1
                LEFT JOIN [x_year_data] as year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")

            WHERE 
                %where%
                AND year_data.value IS NOT NULL

            GROUP BY 
                user_surname, user_name, user_fathers_name, user_tg_id
            FIN;
    
    const SQL_QUERY_OBJECTS_DELAYED = <<<FIN
                SELECT 
                    0 as responsible_id,
                    1 as delay_type,
                    object.name as object_name,
                    object.moge_in_plan as date_planned
                FROM
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    object.moge_in_plan IS NOT NULL 
                    AND object.moge_in_plan < :current_date
                    AND object.moge_in_fact IS NULL
                    AND year_data.value IS NOT NULL

                UNION ALL

                SELECT
                    0,
                    2,
                    object.name,
                    object.moge_out_plan
                FROM
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE
                    object.moge_out_plan IS NOT NULL 
                    AND object.moge_out_plan < :current_date
                    AND object.moge_out_fact IS NULL
                    AND year_data.value IS NOT NULL

                UNION ALL

                SELECT
                    0,
                    3,
                    object.name,
                    object.rgmin_in_plan
                FROM
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    object.rgmin_in_plan IS NOT NULL 
                    AND object.rgmin_in_plan < :current_date
                    AND object.purchase_level = 2
                    AND object.rgmin_in_fact IS NULL
                    AND year_data.value IS NOT NULL

                UNION ALL

                SELECT
                    0,
                    4,
                    object.name,
                    object.rgmin_in_plan
                FROM
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    object.rgmin_in_plan IS NOT NULL 
                    AND object.rgmin_in_plan < :current_date
                    AND object.purchase_level = 1
                    AND object.rgmin_in_fact IS NULL
                    AND year_data.value IS NOT NULL

                UNION ALL

                SELECT
                    0,
                    5,
                    object.name,
                    object.psmr_plan
                FROM
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    object.psmr_plan IS NOT NULL 
                    AND object.psmr_plan < :current_date
                    AND object.psmr_fact IS NULL
                    AND year_data.value IS NOT NULL

                UNION ALL

                SELECT
                    0,
                    6,
                    object.name,
                    object.ksmr_plan
                FROM
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    object.ksmr_plan IS NOT NULL 
                    AND object.ksmr_plan < :current_date
                    AND object.ksmr_fact IS NULL
                    AND year_data.value IS NOT NULL

                UNION ALL

                SELECT
                    0,
                    7,
                    object.name,
                    object.open_date_planned
                FROM
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    object.open_date_planned IS NOT NULL 
                    AND object.open_date_planned < :current_date
                    AND object.open_date_fact IS NULL
                    AND year_data.value IS NOT NULL

                FIN;

    const SQL_QUERY_OBJECTS = <<<FIN
                SELECT 
                    0 as responsible_id,
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
                    [x_object] as object
                    LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = %current_year% AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                WHERE 
                    year_data.value IS NOT NULL
                GROUP BY 
                    object.id
                FIN;    
    
    #[\Override]
    protected function initParams() {
        $this->params = [
            // new \losthost\BlagoBot\params\ParamDescriptionMessageType($this),
        ];
    }
    
    #[\Override]
    protected function getWhere($params) {
        return 'user.tg_user = '. Bot::$user->id;
    }
    
    #[\Override]
    protected function prepareAndSendMessages($params): array {
        if (empty($params['msgtype'])) {
            $params['msgtype'][0] = 89;
        }
        return parent::prepareAndSendMessages($params);
    }
    
    #[\Override]
    protected function resultType(): int|string {
        return self::RESULT_TYPE_NONE;
    }
    
    #[\Override]
    protected function getDelayTemplateName(): string {
        return 'tpl_delays_total.php';
    }
}
