<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\view\ReportSetupView;
use losthost\telle\Bot;
use losthost\BlagoBot\data\report;

class CallbackReport extends AbstractHandlerCallback{

    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        if (preg_match('/^report_/', $callback_query->getData())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $m = [];
        preg_match('/^report_(\d+)$/', $callback_query->getData(), $m);
        
        $report = new report(['id' => $m[1]]);
        $view = new ReportSetupView($report);
        $view->show($callback_query->getMessage()->getMessageId());
        
        Bot::$session->set('data', []);
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
}
