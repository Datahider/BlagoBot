<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\user;
use losthost\DB\DB;
use function \losthost\BlagoBot\showNoUser;
use function \losthost\BlagoBot\showUser;

class CommandDigits extends AbstractHandlerMessage {
    
    protected $id;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $m = [];
        if (preg_match("/^\/(\d+)$/", $message->getText(), $m)) {
            $this->id = (int)$m[1];
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        global $b_user;
        
        $user = new user(['id' => $this->id], true);
        if ($b_user->access_level != user::AL_ADMIN) {
            
        } elseif ($user->isNew()) {
            showNoUser($user);
        } else {
            showUser($user);
        }
        return true;
    }
        
}
