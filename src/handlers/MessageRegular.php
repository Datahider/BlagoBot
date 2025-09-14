<?php
namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BlagoBot\service\AIGateway;
use losthost\DB\DB;
use losthost\telle\Bot;
use losthost\BlagoBot\data\ai_context;
use losthost\templateHelper\Template;
use losthost\BlagoBot\service\TableMap;
use losthost\BlagoBot\reports\ReportObjectsByOmsu;
use losthost\telle\model\DBSession;

use losthost\BlagoBot\service\AIToolCaller;

use function \losthost\BlagoBot\sendMessageWithRetry;

class MessageRegular extends AbstractHandlerMessage {
    
    #[\Override]
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText()) {
            return true;
        }
        return false;
    }

    #[\Override]
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        global $b_user;

        $user_message = [
            'role' => 'user',
            'text' => $message->getText()
        ];
        
        if ($message->getReplyToMessage() && $message->getReplyToMessage()->getDocument()) {
            $file_id = $message->getReplyToMessage()->getDocument()->getFileId();
            $user_message['text'] .= "\n\nИдентификатор файла «{$file_id}»";
        }
        
        if ($message->getReplyToMessage() && $reply_to_text = $message->getReplyToMessage()->getText()) {
            $user_message['text'] .= "\n\n". $this->ClassifyText($reply_to_text);
        }
        
        $this->addToContext($b_user->id, $user_message['role'], $user_message['text'], $user_message);
        $functions = $this->getFunctions();
        
        $ai = new AIGateway();
        
        $add_file_id = false;
        
        while (true) {
    
            $messages = $this->getContext();
            $result = $ai->completion($messages, $functions);

            if ($result['error']) {
                $this->reportErrorToUser($result);
                return true;
            }

            $model_message = $result['message'];
            if (isset($model_message['text'])) {
                $this->addToContext($b_user->id, $model_message['role'], $model_message['text'], $model_message);
                if ($add_file_id) {
                    $fake_message_1 = ['role' => 'user', 'text' => "Хорошо. Идентификатор этого файла «{$add_file_id}»"];
                    $fake_message_2 = ['role' => 'assistant', 'text' => "Понял, идентификатор файла сохранён."];
                    $this->addToContext($b_user->id, $fake_message_1['role'], $fake_message_1['text'], $fake_message_1);
                    $this->addToContext($b_user->id, $fake_message_2['role'], $fake_message_2['text'], $fake_message_2);
                    $add_file_id = false;
                }
                break;
            } elseif (isset($model_message['toolCallList'])) {
                $this->addToContext($b_user->id, $model_message['role'], '[Вызов функции]', $model_message);
                $call_result = $this->toolCalls($model_message['toolCallList']['toolCalls']);
                $this->addToContext($b_user->id, $call_result['role'], '[Результат функции]', $call_result);
                
                foreach ($call_result['toolResultList']['toolResults'] as $tool_result) {
                    $m = [];
                    if ($tool_result['functionResult'] && preg_match("/file_id=(\S+)/", $tool_result['functionResult']['content'], $m)) {
                        $add_file_id = $m[1];
                        break;
                    }
                }
            } else {
                sendMessageWithRetry(Bot::$chat->id, "Получен непредусмотренный ответ модели:\n\n". json_encode($model_message), null);
                return true;
            }

        };
        
        sendMessageWithRetry(Bot::$chat->id, $model_message['text'], null);
        
        return true;
    }
    
    protected function getFunctions() : array {

        $functions_template = new Template('functions.php', Bot::$language_code);
        $functions_template->setTemplateDir('src/templates');
        
        $omsu_map = new TableMap('x_omsu', 'id', 'name');
        $category_map = new TableMap('x_category', 'id', 'name');
        $winners_map = new TableMap('x_contragent', 'id', 'name');
        $responsibles_map = new TableMap('x_responsible_view', 'id', 'fio');
        $user_map = new TableMap('user_view', 'id', 'full_name');
        
        $vars = [
            'omsus' => $omsu_map->values(),
            'categories' => $category_map->values(),
            'winners' => $winners_map->values(),
            'responsibles' => $responsibles_map->values(),
            'users' => $user_map->values()
        ];
        
        foreach ($vars as $key => $value) {
            $functions_template->assign($key, $value);
        }
        
        return unserialize($functions_template->process());
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
    
    protected function contextCountMessages() : int {
        global $b_user;
        
        $sql = $this->sqlContextCountMessages();
        
        $sth = DB::prepare($sql);
        $sth->execute([$b_user->id, $b_user->ai_context_starts->format(DB::DATE_FORMAT)]);
        
        $row = $sth->fetch(\PDO::FETCH_NUM);
        return $row[0];
        
    }
    
    protected function sqlContextCountMessages() {
        return <<<FIN
            SELECT
                COUNT(id)
            FROM
                [ai_context]
            WHERE
                user_id = ?
                AND date_added >= ?
            FIN;
    }
    
    protected function getContext() : array {
        global $b_user;
        
        if ($this->contextCountMessages() == 0) {
            $this->makePrompt();
        }
        
        $sql = $this->sqlGetContext();
        
        $sth = DB::prepare($sql);
        $sth->execute([$b_user->id, $b_user->ai_context_starts->format(DB::DATE_FORMAT)]);
        $query_result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($query_result as $row) {
            $result[] = unserialize($row['data']);
        }
        
        return $result;
    }
    
    protected function sqlGetContext() : string {
        return <<<FIN
            SELECT
                data
            FROM
                [ai_context]
            WHERE
                user_id = ?
                AND date_added >= ?
            ORDER BY date_added
            FIN;
    }
    
    protected function makePrompt() {
        global $b_user;
        
        $prompt = [
            'role' => 'system',
            'text' => $this->getPromptText()
        ];
        
        $ai_context = new ai_context();
        $ai_context->date_added = date_create();
        $ai_context->user_id = $b_user->id;
        $ai_context->role = $prompt['role'];
        $ai_context->text = $prompt['text'];
        $ai_context->data = serialize($prompt);
        $ai_context->write();

    }

    protected function getPromptText() {
        
        global $b_user;
        
        $prompt_template = new Template('prompt.php', Bot::$language_code);
        $prompt_template->setTemplateDir('src/templates');
        
        $vars = [
            'name' => $b_user->name,
            'fathers_name' => $b_user->fathers_name,
            //'json' => $this->getJsonData()    
        ];
        
        foreach ($vars as $key => $value) {
            $prompt_template->assign($key, $value);
        }
        
        return $prompt_template->process();
    }
    
    protected function addToContext($user_id, $role, $text, $data) {
        
        if ($this->contextCountMessages() == 0) {
            $this->makePrompt();
        }
        
        $ai_context = new ai_context();
        $ai_context->date_added = date_create();
        $ai_context->user_id = $user_id;
        $ai_context->role = $role;
        $ai_context->text = $text;
        $ai_context->data = serialize($data);
        $ai_context->write();
        
        return $ai_context;
    }
    
    protected function toolCalls(array $tool_calls) {
        $caller = new AIToolCaller();
        $results_array = $caller->getToolResults($tool_calls);
        
        $call_result = [
            'role' => 'user',
            'toolResultList' => [
                'toolResults' => $results_array
            ]
        ];
        
        return $call_result;
    }
    
    protected function ClassifyText($text) {
        $prompt = $this->getClassificatorPrompt();
        
        $messages = [
            ['role' => 'system', 'text' => $prompt],
            ['role' => 'user', 'text' => $text]
        ];
        
        $ai = new AIGateway();
        $result = $ai->completion($messages);

        if ($result['error']) {
            return '';
        }

        $model_message = $result['message'];
        if (isset($model_message['text'])) {
            if (trim($model_message['text']) == '???') {
                return '';
            } else {
                return $model_message['text'];
            }
        }
        
        return '';
    }
    
    protected function getClassificatorPrompt() {
        
        global $b_user;
        
        $prompt_template = new Template('classificator_prompt.php', Bot::$language_code);
        $prompt_template->setTemplateDir('src/templates');
        
        return $prompt_template->process();
    }
}
