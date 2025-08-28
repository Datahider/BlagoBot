<?php

namespace losthost\BlagoBot\service;

use losthost\telle\Bot;

use function \losthost\BlagoBot\sendMessageWithRetry;

class AIGateway {
    
    protected string $api_key;
    protected string $api_url;
    protected string $model;


    public function __construct() {

        require 'etc/bot_config.php';
        
        $this->api_key = $ai_key;
        $this->api_url = $ai_url;
        $this->model = $ai_model;
    }
    
    public function completion(array $context, ?array $functions=null) {
        $data = [
            'modelUri' => $this->model,
            'completionOptions' => [
                'stream' => false,
                'temperature' => Bot::param('ai_temperature', 0.3),
                'maxTokens' => Bot::param('ai_maxtokens', 2000)
            ],
            'messages' => $context
        ];
        
        if (!is_null($functions)) {
            $data['tools'] = $functions;
        }
        
        return $this->call('/completion', $data);
    }
    
    protected function call(string $function, array $params) {

        $result = $this->post("$this->api_url$function", $params);
        
        // === Обработка возможных ошибок ===
        if ($result['response'] === false) {
            return $this->curlError($result['error']);
        }

        if ($result['http_code'] !== 200) {
            $decoded = json_decode($result['response'], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                $error = $decoded['message'];
            } else {
                $error = $result['response'];
            }
            return $this->httpError($error);
        }

        // === Если всё ок — выводим результат ===
        $decoded = json_decode($result['response'], true);
        if (isset($decoded['result']['alternatives'][0]['message'])) {
            return $this->ok($decoded['result']['alternatives'][0]['message']);
        } else {
            return $this->modelError(print_r($decoded, true));
        }
    }
    
    protected function post($url, $data) {
        
        $headers = [
            "Authorization: Api-Key $this->api_key",
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
    
    protected function curlError($error_text) : array {
        return [
            'error' => true,
            'type' => 'curl',
            'description' => $error_text
        ];
    }

    protected function httpError($error_text) : array {
        return [
            'error' => true,
            'type' => 'http',
            'description' => $error_text
        ];
    }

    protected function modelError($error_text) : array {
        return [
            'error' => true,
            'type' => 'model',
            'description' => $error_text
        ];
    }
    
    protected function ok($message) {
        return [
            'error' => false,
            'message' => $message
        ];
    }
}

