<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;

use function \losthost\BlagoBot\initBUser;

abstract class AbstractMyCommand extends AbstractHandlerCommand {
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        global $b_user;
        initBUser();
        
        return true;
        
    }
}
