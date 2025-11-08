<?php
namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use TelegramBot\Api\Types\Voice;
use losthost\BlagoBot\service\YandexSpeachKitGateway;
use losthost\telle\Bot;
use losthost\BlagoBot\service\IAMToken;

use function \losthost\BlagoBot\sendMessageWithRetry;

class MessageVoice extends AbstractHandlerMessage {
    
    const MAX_BYTES = 10485760;

    protected Voice $voice;

    #[\Override]
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        $voice = $message->getVoice();
        if (!$voice) {
            return false;
        }
        
        $this->voice = $voice;
        return true;
    }

    #[\Override]
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {

        if ($this->voice->getFileSize() > static::MAX_BYTES) {
            sendMessageWithRetry(Bot::$user->id, __('Размер записи голоса превышает максимальный.'), null);
            return true;
        }
        
        $data = $this->getVoiceData();
        $text = $this->recognizeData($data);

        sendMessageWithRetry(Bot::$chat->id, "🗣 $text", null);
        $message->setText($text);
        
        $regular = new MessageRegular();
        
        return $regular->handleUpdate($message);
        
    }
    
    protected function getVoiceData() {
        $file_id = $this->voice->getFileId();
        $file = Bot::$api->getFile($file_id);
        
        require 'etc/bot_config.php'; // $token definition's there
        $data = file_get_contents("https://api.telegram.org/file/bot$token/". $file->getFilePath());
        
        if ($data === false) {
            throw new \Exception('Ошибка получения файла.');
        }
        
        return $data;
    }
    
    protected function recognizeData($data) {
        
        require 'etc/bot_config.php';
        
        $recognizer = new YandexSpeachKitGateway($search_folder_id, $this->getToken());

        return $recognizer->recognize($data);
    }
    
    protected function getToken() {
        $token = new IAMToken();
        return $token->get();
    }
    
}
