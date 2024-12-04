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
use losthost\DB\DBValue;
use losthost\BlagoBot\data\user;
use losthost\BlagoBot\data\x_omsu;
use Exception;
use losthost\BlagoBot\params\AbstractParamDescription;
use losthost\BlagoBot\data\report_param;

class CallbackInlineButton extends AbstractHandlerCallback {
    
    protected $button;
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        try {
            $this->button = new InlineButton($callback_query->getData());
        } catch (Exception $ex) {
            Bot::logException($ex);
            return false;
        }
        return true;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        
        try {
            $error = null;
            $this->processQuery($callback_query);
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            Bot::logException($ex);
        } catch (\TypeError $ex) {
            $error = $ex->getMessage();
            Bot::logException($ex);
        }
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId(), $error); } catch (\Exception $e) {}
        return true;
    }
    
    protected function processQuery(\TelegramBot\Api\Types\CallbackQuery &$callback_query) {
        
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

                // TODO - Сделать проверку на наличие класса хендлера отчета. 
                if (!$report->setDefaultParamValues()) {
                    $this->setDefaultValues($report);
                }
                $view = new ReportSetupView($report);
                $view->show($callback_query->getMessage()->getMessageId());
                break;
            case InlineButton::MB_PARAM:
                $view = new ReportParamView($this->button->getObject());
                $view->show($callback_query->getMessage()->getMessageId());
                break;
            case InlineButton::MB_NEW_PARAM:
                $view = new ReportParamView($this->button->getObject());
                $view->show($callback_query->getMessage()->getMessageId());
                break;
            case InlineButton::MB_VALUE:
            case InlineButton::MB_NEW_VALUE:
                $this->updateParamStoredData();
                
                if (is_a($this->button->getParam(), AbstractParamDescription::class)) {
                    $report = new report(['handler_class' => $this->button->getParam()->getReportClass()]);
                } else {
                    $report = new report(['id' => $this->button->getParam()->report]);
                }

                if ($report->isFastSelect()) {
                    $builder_class = $report->handler_class;
                    $builder = new $builder_class;
                    $view = new ReportResultView($builder);
                    $view->show($callback_query->getMessage()->getMessageId());
                    Bot::$session->set('data', []);
                } elseif ($report->hasOneParam()) {
                    $view = new ReportSetupView($report);
                    $view->show($callback_query->getMessage()->getMessageId());
                } elseif ($this->button->getParam()->isMultipleChoice()) {
                    $view = new ReportParamView($this->button->getParam());
                    $view->show($callback_query->getMessage()->getMessageId());
                } else {
                    $view = new ReportSetupView($report);
                    $view->show($callback_query->getMessage()->getMessageId());
                }
                break;
                
        }
    }
    
    protected function setDefaultValues(report $report) {
        global $b_user;
        
        $param_values = Bot::$session->get('data');
        
        $params = $report->paramsArray();
        
        foreach ($params as $param) {
            if (isset($param_values[$param->name])) {
                continue;
            }
            
            if ($param->name === 'omsu' && $b_user->access_level === user::AL_RESTRICTED) {
                $values = new DBList(x_omsu::class, 'head_id = ? OR vicehead_id = ?', [$b_user->id, $b_user->id]); 
            } elseif (is_a($param->value_set, \losthost\DB\DBObject::class, true)) {
                $class = $param->value_set;
                $values = new DBList($class, "0", []); /// Не выбираем значения. Это просто историческая строчка
            } else {
                
                $values = new DBList(report_param_value::class, "value_set = ? AND is_active = 1 AND is_default = 1 ORDER BY sort, title", $param->value_set);
            }    
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
        
        $param_value = $value->getId();
        if ($param_value == '<=reverse=>') {
            $new_values = isset($param_values[$param->getName()]) ? $param_values[$param->getName()] : [];
            foreach ($param->getValueSet() as $v) {
                $found = array_search($v->getId(), $new_values);
                if ($found === false) {
                    $new_values[] = $v->getId();
                } else {
                    unset($new_values[$found]);
                }
            }
            $param_values[$param->getName()] = $new_values;
        } elseif (empty($param_values[$param->getName()]) || !$param->isMultipleChoice()) {
            $param_values[$param->getName()] = [$param_value];
        } else {
            $found = array_search($param_value, $param_values[$param->getName()]);
            if ($found === false) {
                $param_values[$param->getName()][] = $param_value;
            } else {
                unset($param_values[$param->getName()][$found]);
            }
        }
        Bot::$session->set('data', $param_values);
    }
    
    protected function getValues($param) {
        if (is_a($param, report_param::class)) {
            
        } else {
            $param->getValueSet();
        }
    }
}
