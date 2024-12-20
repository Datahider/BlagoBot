<?php

namespace losthost\BlagoBot\view;

use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param;
use losthost\BotView\BotView;
use losthost\telle\Bot;
use losthost\BlagoBot\data\user;
use losthost\DB\DBList;
use losthost\BlagoBot\data\x_omsu;
use losthost\BlagoBot\params\AbstractParamDescription;

class ReportParamView {
    
    protected $param;
    
    public function __construct(report_param|AbstractParamDescription $param) {
        $this->param = $param;
    }
    
    public function button() {
        
        $button = [
            'text' => $this->buttonIcon(). $this->param->getTitle(),
            'callback_data' => 'param_'. $this->param->id
        ];
        return $button;
        
    }
    
    public function show(int $message_id) {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('tpl_report_param', 'kbd_report_param', $this->viewData(), $message_id);
    }
    
    public function valuesKeyboard() {
        
    }
    
    public function valueButton($value) {
        return ['text' => $value, 'callback_data' => "value_{$this->param->id}_$value"];
    }
    
    protected function viewData() {
        
        if (is_a($this->param, AbstractParamDescription::class)) {
            return $this->newViewData();
        }

        global $b_user;
        
        $data['report'] = new report(['id' => $this->param->report]);
        $data['param'] = $this->param;
        if ($this->param->name == 'omsu' && $b_user->access_level == user::AL_RESTRICTED) {
            $omsus = new DBList(x_omsu::class, 'head_id = ? OR vicehead_id = ?', [$b_user->id, $b_user->id]); 
            $data['values'] = $omsus->asArray();
        } else {
            $data['values'] = $this->param->valuesArray();
        }
        return $data;
    }
    
    protected function newViewData() {
        $report_class = $this->param->getReportClass();
        $data['report'] = new report(['handler_class' => $report_class]);
        $data['param'] = $this->param;
        $data['values'] = $this->param->getValueSet();
        return $data;
    }
}
