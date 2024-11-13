<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\service\DBUpdater;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use TelegramBot\Api\Types\Document;
use losthost\BlagoBot\data\user;
use losthost\telle\model\DBBotParam;
use Exception;

use function \losthost\BlagoBot\__;

class MessageFile extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        static::unsetPriority();
        if ($message->getDocument()) {
            return true;
        }
        return false;
    }

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
            $updater = new DBUpdater();
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
            $db_date = new DBBotParam('db_date');
            $db_date->value = date_create()->format('d.m.Y');
            $view->show('tpl_info', null, ['type' => 'info', 'text' => __('Загрузка данных завершена.')]);
        }
        
        return true;
    }
    
    protected function downloadFile(Document $doc) {
        include 'etc/bot_config.php';
        $file_id = $doc->getFileId();
        $file = Bot::$api->getFile($file_id);
        $contents = file_get_contents("https://api.telegram.org/file/bot$token/". $file->getFilePath());
        
        $local_path = tempnam('/tmp', 'xls');
        file_put_contents($local_path, $contents);
        
        return $local_path;
    }
}
