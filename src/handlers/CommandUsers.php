<?php

namespace losthost\BlagoBot\handlers;

use losthost\BlagoBot\handlers\AbstractMyCommand;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\reports\ReportUsers;
use losthost\BlagoBot\view\ReportAdminPdf;
use losthost\BlagoBot\service\AccessChecker;
use losthost\BlagoBot\data\user;

use function \losthost\BlagoBot\showAdminsOnly;

class CommandUsers extends AbstractMyCommand {

    const COMMAND = 'users';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        parent::handle($message);
        
        $access = new AccessChecker(user::AL_ADMIN);
        if ($access->isDenied()) {
            showAdminsOnly();
            return true;
        }
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
