<?php

namespace losthost\BlagoBot\handlers;

use losthost\BlagoBot\handlers\AbstractMyCommand;
use losthost\BlagoBot\service\AccessChecker;
use losthost\BlagoBot\data\user;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\BlagoBot\initBUser;

class CommandHelp extends AbstractMyCommand {
    
    const COMMAND = 'help';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        global $b_user;
        
        parent::handle($message);
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('cmd_help_'. $b_user->access_level);
        
        return true;
    }
}
