<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\view\InlineButton;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\view\MenuView;
use losthost\BlagoBot\view\ReportSetupView;
use losthost\BlagoBot\view\ReportParamView;
use losthost\BlagoBot\view\ReportResultView;
use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param_value;
use losthost\DB\DBList;
use Exception;

class CallbackInlineButton extends AbstractHandlerCallback {
    
    protected $button;
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        try {
            $this->button = new InlineButton($callback_query->getData());
        } catch (Exception $ex) {
            return false;
        }
        return true;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        
        switch ($this->button->getType()) {
            case InlineButton::MB_SUBMENU:
                $view = new MenuView($this->button->getObject());
                $view->show($callback_query->getMessage()->getMessageId());
                break;
            case InlineButton::MB_REPORT:
                $report = $this->button->getObject();

                $last_report = Bot::$session->get('command');
                if ($report->id <> $last_report) {
                    Bot::$session->set('data', []);
                    Bot::$session->set('command', $report->id);
                }

                $this->setDefaultValues($report);
                $view = new ReportSetupView($report);
                $view->show($callback_query->getMessage()->getMessageId());
                break;
            case InlineButton::MB_PARAM:
                $view = new ReportParamView($this->button->getObject());
                $view->show($callback_query->getMessage()->getMessageId());
                break;
            case InlineButton::MB_VALUE:
                $this->updateParamStoredData();
                
                $report = new report(['id' => $this->button->getParam()->report]);

                if ($report->isFastSelect()) {
                    $builder_class = $report->handler_class;
                    $builder = new $builder_class;
                    $view = new ReportResultView($builder);
                    $view->show($callback_query->getMessage()->getMessageId());
                    Bot::$session->set('data', []);
                } elseif ($report->hasOneParam()) {
                    $view = new ReportSetupView($report);
                    $view->show($callback_query->getMessage()->getMessageId());
                } elseif ($this->button->getParam()->is_multiple_choise) {
                    $view = new ReportParamView($this->button->getParam());
                    $view->show($callback_query->getMessage()->getMessageId());
                } else {
                    $view = new ReportSetupView($report);
                    $view->show($callback_query->getMessage()->getMessageId());
                }
                break;
        }
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
    
    protected function setDefaultValues(report $report) {
        
        $param_values = Bot::$session->get('data');
        
        $params = $report->paramsArray();
        
        foreach ($params as $param) {
            if (isset($param_values[$param->name])) {
                continue;
            }
            
            $values = new DBList(report_param_value::class, "value_set = ? AND is_active = 1 AND is_default = 1 ORDER BY sort, title", $param->value_set);
            $values_array = $values->asArray();
            
            if (count($values_array) == 0) {
                continue;
            }
            
            foreach ($values_array as $value) {
                $param_values[$param->name][] = $value->id;
            }
            Bot::$session->set('data', $param_values);
        }
        
    }
    
    protected function updateParamStoredData() {
        $param_values = Bot::$session->get('data');
        
        $param = $this->button->getParam();
        $value = $this->button->getObject();
        
        $param_value = $value->id;
        if (empty($param_values[$param->name]) || !$param->is_multiple_choise) {
            $param_values[$param->name] = [$param_value];
        } else {
            $found = array_search($param_value, $param_values[$param->name]);
            if ($found === false) {
                $param_values[$param->name][] = $param_value;
            } else {
                unset($param_values[$param->name][$found]);
            }
        }
        Bot::$session->set('data', $param_values);
    }
}
