<?php

namespace losthost\BlagoBot\background;

use losthost\BlagoBot\data\report;
use losthost\telle\Bot;
use losthost\BlagoBot\view\ReportResultView;
use losthost\telle\model\DBChat;
use TelegramBot\Api\Types\Chat;
use losthost\BlagoBot\data\user;
use losthost\telle\model\DBSession;

class ReportSenderOPZ extends AbstractReportSender {
    
    #[\Override]
    public function run() {
    
        global $b_user;
        $b_user = new user(['id' => 4]);
        
        $report = new report(['id' => 24]); 
        
        $group_id = (int)$this->param;
        $chat = Chat::fromResponse([
            'id' => $group_id,
            'type' => 'supergroup'
        ]);
        
        Bot::$chat = new DBChat($chat);
        Bot::$session = new DBSession(1, Bot::$chat);
        Bot::$language_code = 'ru';
        
        Bot::$session->data = [
            'filter' => ['>'],
        ];
        
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
}
