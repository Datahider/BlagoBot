<?php

namespace losthost\BlagoBot\handlers;

use losthost\BlagoBot\handlers\AbstractMyCommand;
use losthost\BlagoBot\reports\ReportStat;
use losthost\BlagoBot\view\ReportAdminPdf;
use losthost\BlagoBot\service\AccessChecker;
use losthost\BlagoBot\data\user;

use function \losthost\BlagoBot\initBUser;

use function \losthost\BlagoBot\showAdminsOnly;

class CommandStat extends AbstractMyCommand {

    const COMMAND = 'stat';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {

        parent::handle($message);
        
        $access = new AccessChecker(user::AL_ADMIN);
        if ($access->isDenied()) {
            showAdminsOnly();
            return true;
        }
        $builder = new ReportStat();

        $view = new ReportAdminPdf($builder);
        $view->show();

        return true;
        
    }
}
