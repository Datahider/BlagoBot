<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\view\ReportResultView;
use losthost\BlagoBot\data\report;
use losthost\telle\Bot;
use losthost\DB\DBList;
use losthost\BlagoBot\data\report_param;

class CallbackMakeReport extends AbstractHandlerCallback {

    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        if (preg_match('/^makereport_\d+$/', $callback_query->getData())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $m = [];
        preg_match('/^makereport_(\d+)$/', $callback_query->getData(), $m);
        
        $report = new report(['id' => $m[1]]);
        
        if ($this->mandatoryParamsOk($report)) {
            $builder_class = $report->handler_class;
            $builder = new $builder_class;

            $view = new ReportResultView($builder);
            $view->show($callback_query->getMessage()->getMessageId());

            Bot::$session->set('data', []);
            $error = null;
            $showAlert = false;
        } else {
            $error = 'Заданы не все обязательные параметры!';
            $showAlert = true;
        }
        
        try { 
            Bot::$api->answerCallbackQuery($callback_query->getId(), $error, $showAlert); 
        } catch (\Exception $e) {}
        return true;
    }
    
    protected function mandatoryParamsOk(report $report) {
        
        $report_handler_class = $report->handler_class;
        $report_handler = new $report_handler_class();
        if ($report_handler->getParams() !== null) {
            return $report_handler->areMandatoryOk();
        }
        
        $mandatory = new DBList(report_param::class, ['report' => $report->id, 'is_mandatory' => 1]);
        $param_data = Bot::$session->get('data');
        foreach ($mandatory->asArray() as $param) {
            if (empty($param_data[$param->name])) {
                return false;
            }
        }
        return true;
    }
}
