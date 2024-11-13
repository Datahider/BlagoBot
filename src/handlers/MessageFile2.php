<?php

namespace losthost\BlagoBot\handlers;

use losthost\BlagoBot\service\DBUpdater2;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use TelegramBot\Api\Types\Document;
use losthost\BlagoBot\data\user;
use Exception;
use losthost\telle\model\DBBotParam;

use function \losthost\BlagoBot\__;

class MessageFile2 extends MessageFile {
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        global $b_user;

        $view = new BotView(Bot::$api, Bot::$user->id, Bot::$language_code);
        
        if ($b_user->access_level != user::AL_ADMIN) {
            $view->show('tpl_admins_only');
            return true;
        }
        
        $has_error = false;
        try {
            $file_path = $this->downloadFile($message->getDocument());
            $updater = new DBUpdater2();
            $view->show('tpl_info', null, ['type' => 'info', 'text' => __('Начинается загрузка файла в БД. Это может занять некоторое время. По окончании загрузки вам придёт ещё одно сообщение')]);
            $updater->update($file_path);
        } catch (Exception $exc) {
            $view->show('tpl_info', null, ['type' => 'error','text' => $exc->getMessage()]);
            $has_error = true;
        }

        if (isset($file_path)) {
            unlink($file_path);
        }
        
        if (!$has_error) {
            $status_date = new DBBotParam('status_date');
            $status_date->value = date_create()->format('d.m.Y');
            
            $view->show('tpl_info', null, ['type' => 'info', 'text' => __('Загрузка данных завершена.')]);
        }
        return true;
    }
}
