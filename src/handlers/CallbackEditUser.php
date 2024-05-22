<?php

namespace losthost\BlagoBot\handlers;

use losthost\BlagoBot\handlers\_Callback;
use losthost\BlagoBot\data\user;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\x_omsu;
use losthost\DB\DBList;
use losthost\BlagoBot\service\AccessChecker;

use function \losthost\BlagoBot\showNoUser;
use function \losthost\BlagoBot\showUser;
use function \losthost\BlagoBot\showAdminsOnly;

class CallbackEditUser extends _Callback {
    
    protected string $command;
    protected user $user;
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $m = [];
        if (preg_match("/^edit_([a-z]+)_(\d+)$/", $callback_query->getData(), $m)) {
            $this->command = $m[1];
            $this->user = new user(['id' => $m[2]], true);
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {

        $access = new AccessChecker(user::AL_ADMIN);
        if ($access->isDenied()) {
            showAdminsOnly($callback_query->getMessage()->getMessageId());
            return parent::handle($callback_query);
        }
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        if ($this->user->isNew()) {
            showNoUser($this->user);
        } else {
            switch ($this->command) {
                case 'role':
                    $view->show('tpl_user_edit', 'kbd_user_role', ['user' => $this->user, 'bindings' => $this->user->getBindings()], $callback_query->getMessage()->getMessageId());
                    break;
                case 'bindings':
                    $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                    $view->show('tpl_user_edit', 'kbd_user_bindings', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus], $callback_query->getMessage()->getMessageId());
                    break;
                case 'delete':
                    $view->show('tpl_user_edit', 'kbd_user_delete', ['user' => $this->user, 'bindings' => $this->user->getBindings()], $callback_query->getMessage()->getMessageId());
                    break;
                case 'start':
                    showUser($this->user, $callback_query->getMessage()->getMessageId());
                    break;
                case 'admin':
                    $this->user->access_level = user::AL_ADMIN;
                    showUser($this->user, $callback_query->getMessage()->getMessageId());
                    break;
                case 'user':
                    $this->user->access_level = user::AL_USER;
                    showUser($this->user, $callback_query->getMessage()->getMessageId());
                    break;
                case 'restricted': 
                    $this->user->access_level = user::AL_RESTRICTED;
                    showUser($this->user, $callback_query->getMessage()->getMessageId());
                    break;
            }
            
            $this->user->isModified() && $this->user->write();
        }

        return parent::handle($callback_query);
        
    }
}
