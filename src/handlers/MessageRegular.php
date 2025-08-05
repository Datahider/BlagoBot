<?php
namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\service\AIGateway;
use losthost\DB\DB;
use losthost\telle\Bot;
use losthost\BlagoBot\data\ai_context;

use function \losthost\BlagoBot\sendMessageWithRetry;

class MessageRegular extends AbstractHandlerMessage {
    
    #[\Override]
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        return $message->getText();
    }

    #[\Override]
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        global $b_user;
        
        $messages = $this->makeContext($message->getText());
        
        $ai = new AIGateway();
        $result = $ai->completion($messages);
        
        if (!$result['error']) {
            $this->addToContext($b_user->id, 'assistant', $result['text']);
            sendMessageWithRetry(Bot::$chat->id, $result['text'], null);
        } else {
            $this->reportErrorToUser($result);
        }
        
        return true;
    }
    
    protected function reportErrorToUser($result) {
        switch ($result['type']) {
            case 'curl':
                $header = "Программная ошибка\n\n";
                break;
            case 'http':
                $header = "Ошибка получения данных\n\n";
                break;
            case 'model':
                $header = "Ошибка модели\n\n";
                break;
        }
        
        sendMessageWithRetry(Bot::$chat->id, $header.$result['description'], null);
    }
    
    protected function makeContext($text) : array {
        
        global $b_user;
        
        $current_messages = $this->getContext();
        
        if (count($current_messages) == 0) {
            $prompt = $this->makePrompt();
            $current_messages[] = [
                'role' => $prompt->role,
                'text' => $prompt->text
            ];
        }
        
        $new_message = $this->addToContext($b_user->id, 'user', $text);
        $current_messages[] = [
            'role' => $new_message->role,
            'text' => $new_message->text
        ];
        
        return $current_messages;
    }
    
    protected function getContext() : array {
        global $b_user;
        
        $sql = <<<FIN
            SELECT
                role,
                text
            FROM
                [ai_context]
            WHERE
                user_id = ?
                AND date_added >= ?
            ORDER BY date_added
            FIN;
        
        $sth = DB::prepare($sql);
        $sth->execute([$b_user->id, $b_user->ai_context_starts->format(DB::DATE_FORMAT)]);
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    protected function makePrompt() {
        global $b_user;
        return $this->addToContext($b_user->id, 'system', $this->getPromptText());
    }

    protected function getPromptText() {
        
        global $b_user;
        
        $prompt = Bot::param('ai_prompt_tpl', 'Ты ассистент, работающий в министерстве. Используй официальный стиль общения. Ко мне обращайся на Вы. Называй меня {{name}} {{fathers_name}}. Не забудь поздороваться в первом сообщении.');

        $vars = [
            'name' => $b_user->name,
            'fathers_name' => $b_user->fathers_name
        ];
        
        foreach ($vars as $key => $value) {
            $prompt = str_replace("{{{$key}}}", $value, $prompt);
        }
        
        return $prompt;
    }
    
    protected function addToContext($user_id, $role, $text) {
        
        $ai_context = new ai_context();
        $ai_context->date_added = date_create();
        $ai_context->user_id = $user_id;
        $ai_context->role = $role;
        $ai_context->text = $text;
        $ai_context->write();
        
        return $ai_context;
    }
    
}
