<?php

namespace losthost\BlagoBot\view;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use stdClass;

class ReportResultView {
    
    protected AbstractReport $report;
    protected stdClass $result;
    
    public function __construct(AbstractReport $report) {
        $this->report = $report;
        $this->result = $this->report->build();
    }
    
    public function show(?int $message_id=null) {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        
        if ($this->result->ok) {
            $view->show('tpl_report_view', null, ['result' => $this->result], $message_id);
        } else {
            $view->show('tpl_report_view', null, ['result' => $this->result], $message_id);
        }
        
        Bot::$session->set('command', null);
    }
}
