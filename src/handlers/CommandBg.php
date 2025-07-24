<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;

class CommandBg extends AbstractHandlerCommand {
    
    const COMMAND = 'bg';
    
    #[\Override]
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $process = new \losthost\BlagoBot\background\ReportSenderOPZ('-1001888315905');
        $process->run();
        
        return true;
        
    }
}
