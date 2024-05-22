<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\data\user;
use \losthost\BlagoBot\service\AccessChecker;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\BlagoBot\showAdminsOnly;
use function \losthost\BlagoBot\showNoUser;
use function \losthost\BlagoBot\showUser;

class MessageFIO extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText()) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $access = new AccessChecker(user::AL_ADMIN);
        if ($access->isDenied()) {
            static::unsetPriority();
            showAdminsOnly();
            return true;
        }

        if (preg_match("/^\//", $message->getText())) {
            Bot::$api->deleteMessage(Bot::$chat->id, Bot::$session->get('data')['message_id']);
            static::unsetPriority();
            return false;
        }
        
        $fio = [];
        if (preg_match("/^(\S+)\s+(\S+)\s+(\S+)$/", $message->getText(), $fio)) {
            $user = new user(['id' => Bot::$session->get('data')['user_id']]);
            if ($user->isNew()) {
                showNoUser($user);
            } else {
                $user->surname = $fio[1];
                $user->name = $fio[2];
                $user->fathers_name = $fio[3];
                $user->write();
                showUser($user, Bot::$session->get('data')['message_id']);
                Bot::$api->deleteMessage(Bot::$chat->id, $message->getMessageId());
            }
        } else {
            $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
            $view->show('tpl_info', null, ['type' => 'error', 'text' => 'Не верный формат ФИО. Введите три слова, каждое с большой буквы.'], Bot::$session->get('data')['message_id']);
        }
        
        return true;
    }
}
