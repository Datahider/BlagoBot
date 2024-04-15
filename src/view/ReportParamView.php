<?php

namespace losthost\BlagoBot\view;

use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param;
use losthost\BotView\BotView;
use losthost\telle\Bot;

class ReportParamView {
    
    protected $param;
    
    public function __construct(report_param $param) {
        $this->param = $param;
    }
    
    public function button() {
        
        $button = [
            'text' => $this->buttonIcon(). $this->param->title,
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
        $data['report'] = new report(['id' => $this->param->report]);
        $data['param'] = $this->param;
        $data['values'] = $this->param->valuesArray();
        return $data;
    }
}
