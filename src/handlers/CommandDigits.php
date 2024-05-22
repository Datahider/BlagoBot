<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\user;
use losthost\DB\DB;
use losthost\BlagoBot\service\AccessChecker;
use losthost\BlagoBot\handlers\MessageFIO;

use function \losthost\BlagoBot\showNoUser;
use function \losthost\BlagoBot\showUser;
use function \losthost\BlagoBot\showAdminsOnly;

class CommandDigits extends AbstractHandlerMessage {
    
    protected user $user;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $m = [];
        if (preg_match("/^\/(\d+)$/", $message->getText(), $m)) {
            $this->user = new user(['id' => $m[1]], true);
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $access = new AccessChecker(user::AL_ADMIN);
        if ($access->isDenied()) {
            showAdminsOnly();
            return true;
        }
        
        if ($this->user->isNew()) {
            showNoUser($this->user);
        } else {
            $message_id = showUser($this->user);
            MessageFIO::setPriority(['user_id' => $this->user->id, 'message_id' => $message_id]);
        }
        return true;
    }
        
}
