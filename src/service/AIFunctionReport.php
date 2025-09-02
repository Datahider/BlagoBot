<?php

namespace losthost\BlagoBot\service;

use losthost\telle\Bot;
use losthost\BlagoBot\data\report;
use losthost\BlagoBot\view\ReportResultView;
use losthost\telle\model\DBSession;

abstract class AIFunctionReport extends AIFunction {

    protected $stored_session;
    
    protected function setSession($params) {
        $this->stored_session = Bot::$session;
        Bot::$session = new DBSession(Bot::$user);
        
        Bot::$session->set('data', $params);
    }
    
    protected function sendReport($report_id, $params) : string {
        
        $this->setSession($params);
        
        $report = new report(['id' => $report_id]);
        
        $builder_class = $report->handler_class;
        $builder = new $builder_class;

        $viewer = $builder->getCustomResultViewClass();
        if (!$viewer) {
            $view = new ReportResultView($builder);
        } else {
            $view = new $viewer($builder);
        }
            
        $view->show();
        
        $this->resetSession();
        
        $report_result = $view->getResult();
        
        if ($report_result->ok) {
            $result = "Запрошенный отчет отправлен.";
        } else {
            $result = "При формировании отчета возникли ошибки:\n- ".
            implode("\n- ", $report_result->errors);
            $result = "При формировании отчета возникли ошибки. Список ошибок был отправлен отдельным сообщением.";
        }

        return $result;
        
    }
    
    protected function resetSession() {
        Bot::$session = $this->stored_session;
        Bot::$session->write(); // Возвращаем в базу что было 
    }
    
    protected function mapParam(array|string &$param, array $map) {

        if (is_array($param)) {
            foreach ($param as &$value) {
                if (array_key_exists($value, $map)) {
                    $value = $map[$value];
                }
            }
            unset($value); // Сбрасываем ссылку
        } else {
            if (array_key_exists($param, $map)) {
                $param = $map[$param];
            }
        }
        
    }
}
