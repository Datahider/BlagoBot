<?php

namespace losthost\BlagoBot\handlers;

use losthost\BlagoBot\service\AccessChecker;
use losthost\BlagoBot\data\user;
use losthost\telle\Bot;
use losthost\DB\DBView;

use function \losthost\BlagoBot\showAdminsOnly;

class CommandAILog extends AbstractMyCommand {
    
    const COMMAND = 'ailog';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        parent::handle($message);
        
        $access = new AccessChecker(user::AL_ADMIN);
        if ($access->isDenied()) {
            showAdminsOnly();
            return true;
        }

        if (empty($this->args)) {
            $this->args = '1';
        }
        
        $this->sendFile();
        
        return true;
    }
    
    protected function sendFile() {
        
        $file_data = $this->getData();
        
        $tmp_file = tempnam(sys_get_temp_dir(), 'ailog_');
        file_put_contents($tmp_file, $file_data);
        
        $file_to_send = new \CURLFile($tmp_file, 'text/plain', 'ailog_'. date_create()->format('Y-m-d'). ' ('. $this->args. ').log');        
        Bot::$api->sendDocument(Bot::$chat->id, $file_to_send, 'Лог запросов к ИИ-ассистенту за '. $this->args. ' дней');
    }
    
    protected function getData() {
        $header = 'Ниже находится лог запросов к ИИ-ассистенту за последние '. $this->args. ' дней. Проанализируй и выведи наиболее часто встречающиеся по смыслу запросы.';
        
        $log = [];
        $days_ago = $this->args-1;
        $start = date_create("00:00 $days_ago days ago");
        $view = new DBView('SELECT text FROM [ai_context] WHERE role = "user" AND SUBSTR(text, 1, 1) <> "[" AND date_added >= ?', [$start]);
        
        while ($view->next()) {
            $log[] = $view->text;
        }
        
        return "$header\n\n". implode("\n", $log);
        
    }
}
