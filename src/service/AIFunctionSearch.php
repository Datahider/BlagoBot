<?php

namespace losthost\BlagoBot\service;

use losthost\telle\Bot;
use function \losthost\BlagoBot\sendMessageWithRetry;

class AIFunctionSearch extends AIFunction {

    protected string $search_key;
    protected string $search_url;
    protected string $search_folder_id;
    
    public function __construct() {

        require 'etc/bot_config.php';
        
        $this->search_key = $search_key;
        $this->search_url = $search_url;
        $this->search_folder_id = $search_folder_id;
        
    }
    
    #[\Override]
    public function getResult(array $params): mixed {

        sendMessageWithRetry(Bot::$chat->id, "Ищу в интернете по запросу: «$params[prompt]»...", null);
        
        $result = $this->search($params['prompt']);
        return $result;
    }
    
    protected function search(string $prompt) : string {
        
        $data = [
            'messages' => [
                'content' => $prompt,
                'role' => 'ROLE_USER'
            ],
            'folderId' => $this->search_folder_id,
            'fixMisspell' => true,
            
        ];
        
        $result = $this->post($this->search_url, $data);
        
        if ($result['response'] === false) {
            return "Не удалось получить результат поиска из за ошибки связи.";
        }
        
        if ($result['http_code'] !== 200) {
            return "Не удалось получить результат поиска из-за ошибки на поисковом сервере.";
        }
        
        $decoded = json_decode($result['response'], true);  
        
        return $decoded[0]['message']['content'];
        
    }
        
    protected function post($url, $data) {
        
        $headers = [
            "Authorization: Bearer $this->search_key",
            "Content-Type: application/json",
        ];
        
        $json = json_encode($data);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CAINFO, 'vendor/losthost/telle/src/cacert.pem');  
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $error
        ];
    }
    
}
