<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\view\InlineButton;

class CallbackValue extends AbstractHandlerCallback {

    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        if (preg_match('/^value_\d+_\d+$/', $callback_query->getData())) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $m = [];
        
        $button = new 
        preg_match('/^value_(\d+)$/', $callback_query->getData(), $m);
        
        $report = new report(['id' => $m[1]]);
        $view = new ReportSetupView($report);
        $view->show($callback_query->getMessage()->getMessageId());
        
        Bot::$session->set('data', []);
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
}
