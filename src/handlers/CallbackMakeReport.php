<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\view\ReportResultView;
use losthost\BlagoBot\data\report;
use losthost\telle\Bot;

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
        $builder_class = $report->handler_class;
        $builder = new $builder_class;
        
        $view = new ReportResultView($builder);
        $view->show($callback_query->getMessage()->getMessageId());
        
        Bot::$session->set('data', []);
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
}
