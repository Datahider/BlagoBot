<?php
namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\DB\DBView;
use losthost\DB\DB;
use losthost\telle\Bot;
use losthost\templateHelper\Template;
use losthost\BlagoBot\service\ReportSummary;

use function losthost\BlagoBot\__;
use function \losthost\BlagoBot\sendSplitMessage;

class ReportStatusSender extends AbstractReport {
    
    const DELAY_TYPES = [
        1 => 'Заход в МОГЭ',
        2 => 'Получение заключения МОГЭ',
        3 => 'Заход на согласование РГ ККП',
        4 => 'Заход на согласование в министерство',
        5 => 'Публикация СМР',
        6 => 'Контрактация СМР',
        7 => 'Открытие объекта',
    ];
    
    const QUERY_OMSU = 'omsu';
    const QUERY_OBJECTS = 'objects';
    const QUERY_OBJECTS_DELAYED = 'delays';

    
    protected function checkParamErrors($params): false|array {
        return false;
    }

    protected function reportColumns(): array {
        return [__('ОМСУ'), __('Адресат'), __('Статус')];
    }

    protected function reportData($params): array {
        return $this->prepareAndSendMessages($params);
    }

    protected function resultType(): int {
        return static::RESULT_TYPE_SHOW;
    }
    
    protected function reportSummary($params): ReportSummary {
        return new ReportSummary('Отправка СМС о статусе', date_create_immutable(), $params);
    }
    
    protected function sendCommonStatus($omsu_data) : array {
        $result = [];
        $template = new Template('tpl_common_status.php');
        $template->setTemplateDir('src/templates');
        
        foreach ($omsu_data as $data) {
            if ($data['user_tg_id']) {
                $template->assign('data', $data);
                $msg_text = $template->process();

                try {
                    $recipient = "$data[user_surname] $data[user_name] $data[user_fathers_name]";
                    sendSplitMessage($data['user_tg_id'], $msg_text);
                    $send_result = '✅ Успех';
                } catch (\Exception $e) {
                    $send_result = '⚠️ Ошибка Телеграм';
                }
            } else {
                $recipient = '--НЕ ЗАДАН--';
                $send_result = '🚫 Не может быть отправлено';
            }
                    
            $result[] = [
                $data['omsu_name'],
                $recipient,
                $send_result
            ];
            
        }
        
        return $result;
    }
    
    protected function sendDelays($omsu_data) : array {
        $result = [];
        $template = new Template('tpl_delays.php');
        $template->setTemplateDir('src/templates');
        
        foreach ($omsu_data as $data) {
            
            if ($data['total_delays'] == 0) {
//                if (isset($params['omsu'])) {
                    $result[] = [
                        $data['omsu_name'],
                        'Не отправлялось',
                        '🟢 Риски отстутсвуют'
                    ];
//                }
                continue;
            }
            
            
            if ($data['user_tg_id']) {
                $template->assign('data', $data);
                $msg_text = $template->process();

                try {
                    $recipient = "$data[user_surname] $data[user_name] $data[user_fathers_name]";
                    Bot::$api->sendMessage($data['user_tg_id'], $msg_text, 'HTML');
                    $send_result = '✅ Успех';
                } catch (\Exception $e) {
                    $send_result = '⚠️ Ошибка Телеграм';
                }
            } else {
                $recipient = '--НЕ ЗАДАН--';
                $send_result = '🚫 Не может быть отправлено';
            }
                    
            $result[] = [
                $data['omsu_name'],
                $recipient,
                $send_result
            ];
        }
        return $result;
    }
    
    protected function prepareAndSendMessages($params) : array {
        
        if ($params['msgtype'][0] == 88) {
            $omsu_data = $this->getCommonStatus($params);
            $result = $this->sendCommonStatus($omsu_data);
        } elseif ($params['msgtype'][0] == 89) {
            $omsu_data = $this->getDelays($params);
            $result = $this->sendDelays($omsu_data);
        }
                
        return $result;
    }
    
    protected function getSql($query, $params_recipient, $params_omsu) {
        $recipient = $params_recipient[0] == 86 ? 'head_id' : 'vicehead_id';
        $current_year = date('Y');
        $omsu_where = isset($params_omsu) ? 'omsu.id IN ('. implode(',', $params_omsu). ')' : '1';
    
        $sql = [
            self::QUERY_OMSU => <<<FIN
                    SELECT 
                        omsu.id as omsu_id,
                        omsu.name as omsu_name,
                        user.id as user_id,
                        user.surname as user_surname,
                        user.name as user_name,
                        user.fathers_name as user_fathers_name,
                        user.tg_user as user_tg_id,

                        COUNT(DISTINCT object.uin) as total_objects,
                        SUM(year_data.value) / 1000 as total_limit

                    FROM 
                        [x_omsu] as omsu
                        LEFT JOIN [user] as user ON omsu.$recipient = user.id
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                        LEFT JOIN [x_year_data] as year_data ON object.id = year_data.x_object_id AND year_data.year = $current_year AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")

                    WHERE 
                        $omsu_where
                        AND year_data.value IS NOT NULL

                    GROUP BY 
                        omsu.id, omsu_name, user_id, user_surname, user_name, user_fathers_name, user_tg_id

                    FIN,
            
            self::QUERY_OBJECTS_DELAYED => <<<FIN
                    SELECT 
                        omsu.id as omsu_id,
                        1 as delay_type,
                        object.name as object_name,
                        object.moge_in_plan as date_planned
                    FROM
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                    WHERE 
                        $omsu_where
                        AND object.moge_in_plan IS NOT NULL 
                        AND object.moge_in_plan < :current_date
                        AND object.moge_in_fact IS NULL
                    
                    UNION ALL

                    SELECT 
                        omsu.id,
                        2,
                        object.name,
                        object.moge_out_plan
                    FROM
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                    WHERE
                        $omsu_where
                        AND object.moge_out_plan IS NOT NULL 
                        AND object.moge_out_plan < :current_date
                        AND object.moge_out_fact IS NULL

                    UNION ALL

                    SELECT 
                        omsu.id,
                        3,
                        object.name,
                        object.rgmin_in_plan
                    FROM
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                    WHERE 
                        $omsu_where
                        AND object.rgmin_in_plan IS NOT NULL 
                        AND object.rgmin_in_plan < :current_date
                        AND object.purchase_level = 2
                        AND object.rgmin_in_fact IS NULL

                    UNION ALL

                    SELECT 
                        omsu.id,
                        4,
                        object.name,
                        object.rgmin_in_plan
                    FROM
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                    WHERE 
                        $omsu_where
                        AND object.rgmin_in_plan IS NOT NULL 
                        AND object.rgmin_in_plan < :current_date
                        AND object.purchase_level = 1
                        AND object.rgmin_in_fact IS NULL

                    UNION ALL

                    SELECT 
                        omsu.id,
                        5,
                        object.name,
                        object.psmr_plan
                    FROM
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                    WHERE 
                        $omsu_where
                        AND object.psmr_plan IS NOT NULL 
                        AND object.psmr_plan < :current_date
                        AND object.psmr_fact IS NULL

                    UNION ALL

                    SELECT 
                        omsu.id,
                        6,
                        object.name,
                        object.ksmr_plan
                    FROM
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                    WHERE 
                        $omsu_where
                        AND object.ksmr_plan IS NOT NULL 
                        AND object.ksmr_plan < :current_date
                        AND object.ksmr_fact IS NULL

                    UNION ALL

                    SELECT 
                        omsu.id,
                        7,
                        object.name,
                        object.open_date_planned
                    FROM
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                    WHERE 
                        $omsu_where
                        AND object.open_date_planned IS NOT NULL 
                        AND object.open_date_planned < :current_date
                        AND object.open_date_fact IS NULL

                    FIN,
            
            self::QUERY_OBJECTS => <<<FIN
                    SELECT 
                        omsu.id AS omsu_id,
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
                        [x_omsu] as omsu
                        LEFT JOIN [x_object] as object ON omsu.id = object.omsu_id
                        LEFT JOIN [x_year_data] AS year_data ON object.id = year_data.x_object_id AND year_data.year = $current_year AND year_data.type IN ("Лимит ФБ","Лимит БМ","Лимит БМО","Лимит ОМСУ")
                    WHERE 
                        $omsu_where
                        AND year_data.value IS NOT NULL
                    GROUP BY 
                        object.id
                    FIN    
            
        ];
        
        return $sql[$query];
    }
    
    protected function getCommonStatus($params) {
        $sql_omsu = $this->getSql(self::QUERY_OMSU, $params['recipient'], $params['omsu']);
        $sql_objects = $this->getSql(self::QUERY_OBJECTS, $params['recipient'], $params['omsu']);
        
        $omsu_view = new DBView($sql_omsu);
        $current_date = date_create_immutable()->format(DB::DATE_FORMAT);
        $current_year = date('Y');
        $objects_view = new DBView($sql_objects);
        
        while ($omsu_view->next()) {
            $omsu_data[$omsu_view->omsu_id] = [
                'omsu_id' => $omsu_view->omsu_id,
                'omsu_name' => $omsu_view->omsu_name,
                'user_id' => $omsu_view->user_id,
                'user_surname' => $omsu_view->user_surname,
                'user_name' => $omsu_view->user_name,
                'user_fathers_name' => $omsu_view->user_fathers_name,
                'user_tg_id' => $omsu_view->user_tg_id,
                'total_objects' => $omsu_view->total_objects,
                'total_limit' => $omsu_view->total_limit,
                'current_year' => $current_year,
                'current_date' => $current_date,
                'object_data' => []
            ];
            
        }
        
        while ($objects_view->next()) {
            $omsu_data[$objects_view->omsu_id]['object_data'][] = [
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
        return $omsu_data;
    }
    
    protected function getDelays($params) {
        $sql_omsu = $this->getSql(self::QUERY_OMSU, $params['recipient'], $params['omsu']);
        $sql_objects = $this->getSql(self::QUERY_OBJECTS_DELAYED, $params['recipient'], $params['omsu']);
        
        $omsu_view = new DBView($sql_omsu);
        $current_date = date_create_immutable()->format(DB::DATE_FORMAT);
        $current_year = date('Y');
        $objects_view = new DBView($sql_objects, ['current_date' => $current_date]);
        
        while ($omsu_view->next()) {
            $omsu_data[$omsu_view->omsu_id] = [
                'omsu_id' => $omsu_view->omsu_id,
                'omsu_name' => $omsu_view->omsu_name,
                'user_id' => $omsu_view->user_id,
                'user_surname' => $omsu_view->user_surname,
                'user_name' => $omsu_view->user_name,
                'user_fathers_name' => $omsu_view->user_fathers_name,
                'user_tg_id' => $omsu_view->user_tg_id,
                'total_objects' => $omsu_view->total_objects,
                'total_limit' => $omsu_view->total_limit,
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
            $omsu_data[$objects_view->omsu_id][$objects_view->delay_type][] = [
                'object_name' => $objects_view->object_name,
                'date_planned' => $objects_view->date_planned
            ];
            $omsu_data[$objects_view->omsu_id]['total_delays']++;
        }
    
        return $omsu_data;
    }
}
