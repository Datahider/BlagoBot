<?php

namespace losthost\BlagoBot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\BlagoBot\data\report;
use losthost\telle\Bot;
use losthost\BlagoBot\view\ReportResultView;
use losthost\telle\model\DBChat;
use TelegramBot\Api\Types\Chat;
use losthost\BlagoBot\data\user;
use losthost\telle\model\DBSession;


abstract class AbstractReportSender extends AbstractBackgroundProcess {
 
    #[\Override]
    public function run() {

        global $b_user;
        $b_user = new user(['id' => 4]);

        $params = explode(' ', $this->param);
        $report_id = (int)$params[0];
        $group_id = (int)$params[1];
        $chat = Chat::fromResponse([
            'id' => $group_id,
            'type' => 'supergroup'
        ]);
        
        Bot::$chat = new DBChat($chat);
        Bot::$language_code = 'ru';

        Bot::$session = new DBSession(1, Bot::$chat);
        Bot::$session->data = $this->reportParamSetup();

        $report = new report(['id' => $report_id]);
        
        $builder_class = $report->handler_class;
        $builder = new $builder_class;

        $viewer = $builder->getCustomResultViewClass();
        if (!$viewer) {
            $view = new ReportResultView($builder);
        } else {
            $view = new $viewer($builder);
        }

        $view->show();
        
    }
    
    abstract protected function reportParamSetup(): array;
}
