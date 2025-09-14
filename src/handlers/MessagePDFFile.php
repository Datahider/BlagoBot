<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use TelegramBot\Api\Types\Document;
use losthost\telle\Bot;
use losthost\BlagoBot\data\ai_context;

use function \losthost\BlagoBot\__;
use function \losthost\BlagoBot\sendMessageWithRetry;

class MessagePDFFile extends AbstractHandlerMessage {
    
    const MAX_BYTES = 10485760;
    
    protected Document $document;

    #[\Override]
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
    
        $doc = $message->getDocument();
        if (!$doc) {
            return false;
        }
        
        $ext = pathinfo($doc->getFileName(), PATHINFO_EXTENSION);
        if (strtolower($ext) != 'pdf') {
            return false;
        }
        
        $this->document = $doc;
        return true;
    }

    #[\Override]
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        global  $b_user;
        
        if ($this->document->getFileSize() > static::MAX_BYTES) {
            sendMessageWithRetry(Bot::$user->id, __('Размер PDF-файла превышает максимальный.'), null);
            return true;
        }
        
        $file_name = $this->document->getFileName();
        $data = $this->getDocumentData();
        $text = $this->recognizeData($data);
        
        $b_user->aiContextAddMessage(['role' => 'user', 'text' => <<<FIN
            Вот распознанное текстовое содержимое PDF-файла «{$file_name}»:
            
            ```text
            $text
            ```
                    
            Скажи, что файл получен и поинтересуйся, что я хочу с ним сделать.        
            FIN]);
            
        $answer = $this->getAnswer();    
        $b_user->aiContextAddMessage(['role' => 'assistant', 'text' => $answer]);
        
        sendMessageWithRetry(Bot::$user->id, $answer, 'html');
        return true;
    }
    
    protected function getAnswer() {
        $answer_options = [
            'Содержимое файла успешно распознано. Что вы хотите сделать с этой информацией?',
            'PDF-файл успешно обработан. Что вы хотите сделать с распознанным текстом?',
            'Текстовое содержимое успешно извлечено из PDF. Что вы хотите с ним сделать?',
            'Работа с файлом завершена. Текст распознан. Я готов ответить на ваши вопросы по тексту.',
        ];
        
        return $answer_options[array_rand($answer_options)];
    }
    
    protected function getDocumentData() {
        $file_id = $this->document->getFileId();
        $file = Bot::$api->getFile($file_id);
        
        require 'etc/bot_config.php'; // $token definition's there
        $data = file_get_contents("https://api.telegram.org/file/bot$token/". $file->getFilePath());
        
        if ($data === false) {
            throw new \Exception('Ошибка получения файла.');
        }
        
        return $data;
    }
    
    protected function recognizeData($data) {
        
        $encoded_data = base64_encode($data);
        
        
    }
}
