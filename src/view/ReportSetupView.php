<?php

namespace losthost\BlagoBot\view;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param;
use losthost\DB\DBList;
use losthost\BlagoBot\reports\ReportParams;

class ReportSetupView {

    protected $report;
    
    public function __construct(report $report) {
        $this->report = $report;
    }
    
    public function show(int $message_id) {
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('tpl_report_setup', 'kbd_report_setup', $this->viewData(), $message_id);
    }
    
    public function viewData() {
        
        if ($this->report->handler_param) {
            return $this->newViewData();
        }
        
        $data['report'] = $this->report;

        $params = new DBList(report_param::class, 'report = ? ORDER BY sort', $this->report->id);
        $report_params = $params->asArray();
        $data['report_params'] = $report_params;
        
        if (count($report_params) == 1) {
            $report_param = $report_params[0];
            $data['report_param_values'] = $report_param->valuesArray();
        }
        
        $data['selected_params'] = new ReportParams(); 
        return $data;
    }
    
    public function newViewData() {
        $data['report'] = $this->report;
        $param_handler_class  = $this->report->handler_class;
        $param_handler = new $param_handler_class;
        
        $data['report_params'] = $param_handler->getParams();
        
        if (count($data['report_params']) == 1) {
            $data['report_param_values'] = $data['report_params'][0]->getValueSet();
        }
        
        $data['selected_params'] = new ReportParams(); 
        return $data;
    }
}
