<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\BlagoBot\data\user;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\data\x_omsu;
use losthost\DB\DBList;

use function \losthost\BlagoBot\showNoUser;
use function \losthost\BlagoBot\showUser;

class CallbackEditUser extends AbstractHandlerCallback {
    
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

        global $b_user;
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
            
        if ($b_user->access_level != 'admin') {
            $view->show('tpl_info', ['type' => 'error'])
        } elseif ($this->user->isNew()) {
            showNoUser($user);
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
                    $this->user->access_level = 'admin';
                    showUser($this->user, $callback_query->getMessage()->getMessageId());
                    break;
            }
        }
        
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
        
    }
}
