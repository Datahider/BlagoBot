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
    protected x_omsu $omsu;


    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $m = [];
        if (preg_match("/^edit_([a-z]+)_(\d+)_?(\d*)$/", $callback_query->getData(), $m)) {
            $this->command = $m[1];
            $this->user = new user(['id' => $m[2]]);
            if (!empty($m[3])) {
                $this->omsu = new x_omsu(['id' => $m[3]]);
            }
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
        switch ($this->command) {
            case 'role':
                $view->show('tpl_user_edit', 'kbd_user_role', ['user' => $this->user, 'bindings' => $this->user->getBindings()], $callback_query->getMessage()->getMessageId());
                break;
            case 'bindings':
                $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                $view->show('tpl_bindings_edit', 'kbd_bindings_edit', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus], $callback_query->getMessage()->getMessageId());
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
            case 'unbindhead':
                $this->omsu->head_id = null;
                $this->omsu->write();
                $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                $view->show('tpl_bindings_edit', 'kbd_bindings_edit', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus], $callback_query->getMessage()->getMessageId());
                break;
            case 'unbindvicehead':
                $this->omsu->vicehead_id = null;
                $this->omsu->write();
                $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                $view->show('tpl_bindings_edit', 'kbd_bindings_edit', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus], $callback_query->getMessage()->getMessageId());
                break;
            case 'head':
                $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                $view->show('tpl_bindings_add', 'kbd_bindings_add', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus, 'type' => 'head'], $callback_query->getMessage()->getMessageId());
                break;
            case 'vicehead':
                $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                $view->show('tpl_bindings_add', 'kbd_bindings_add', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus, 'type' => 'vicehead'], $callback_query->getMessage()->getMessageId());
                break;
            case 'bindhead':
                $this->omsu->head_id = $this->user->id;
                $this->omsu->write();
                $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                $view->show('tpl_bindings_add', 'kbd_bindings_add', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus, 'type' => 'head'], $callback_query->getMessage()->getMessageId());
                break;
            case 'bindvicehead':
                $this->omsu->vicehead_id = $this->user->id;
                $this->omsu->write();
                $omsus = (new DBList(x_omsu::class, '1 ORDER BY name', []))->asArray();
                $view->show('tpl_bindings_add', 'kbd_bindings_add', ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'omsus' => $omsus, 'type' => 'vicehead'], $callback_query->getMessage()->getMessageId());
                break;
            case 'end':
                $view->show('tpl_user_edit', null, ['user' => $this->user, 'bindings' => $this->user->getBindings(), 'end' => true], $callback_query->getMessage()->getMessageId());
                MessageFIO::unsetPriority();
                break;
            case 'confirmdelete':
                foreach ($this->user->getBindings() as $binding) {
                    $omsu = new x_omsu(['id' => $binding[0]]);
                    $prop = $binding[2]. '_id';
                    $omsu->$prop = null;
                    $omsu->write();
                } 
                $fio = $this->user->getFIO();
                $id = $this->user->id;
                $this->user->delete();
                MessageFIO::unsetPriority();
                $view->show('tpl_user_deleted', null, ['fio' => $fio, 'id' => $id], $callback_query->getMessage()->getMessageId());
                break;
        }            

        isset($this->user) && $this->user->isModified() && $this->user->write();
        isset($this->omsu) && $this->omsu->isModified() && $this->omsu->write();
        
        return parent::handle($callback_query);
        
    }
}
