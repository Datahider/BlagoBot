<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\reports\ReportUsers;
use losthost\BlagoBot\view\ReportAdminPdf;

class CommandUsers extends AbstractHandlerCommand {

    const COMMAND = 'users';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $builder = new ReportUsers();

        $view = new ReportAdminPdf($builder);
        $view->show();

        return true;
    }
    
    protected function prepareData() : array {
        return [
            'linked' => $this->getLinked(),
            'other' => $this->getOthers() 
        ];
    }
    
    protected function prepareMessages(array $data) : array {
        return [
            'Сообщение 1',
            "Сообщение №2"
        ];
    }
}
