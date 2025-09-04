<?php

namespace losthost\BlagoBot\service;

class YandexARTGateway {
    
    const URL_GENERATE = 'https://llm.api.cloud.yandex.net/foundationModels/v1/imageGenerationAsync';
    const URL_OPERATION_GET = 'https://operation.api.cloud.yandex.net/operations/';
    
    const MIME_JPEG = 'image/jpeg';
    const MIME_PNG = 'image/png';
    
    protected string $folder_id;
    protected string $api_key;
    protected string $model_uri;
    
    public function __construct(string $folder_id, string $api_key) {
        $this->folder_id = $folder_id;
        $this->api_key = $api_key;
        $this->model_uri = "art://{$folder_id}/yandex-art/latest";
    }
    
    public function generate(string|array $prompt, int $width_ratio=1, int $height_ratio=1, string $mime_type=self::MIME_JPEG, int $seed=0) : \stdClass {
    
        if (!is_array($prompt)) {
            $prompt = [$prompt];
        }
        
        $this->checkNormalizePromptArray($prompt);
        
        return $this->generateFromArray($prompt, $width_ratio, $height_ratio, $mime_type, $seed);

    }
    
    public function operationGet(string $operation_id) : \stdClass {
        
        $result = $this->get(static::URL_OPERATION_GET. $operation_id);

        if ($result['http_code'] != 200 && $result['error']) {
            $error_message = "$result[http_code] - $result[error]";
            throw new \Exception($error_message);
        }
        
        return json_decode($result['response']);
        
    }
    
    protected function checkNormalizePromptArray(array &$prompt) : void {
        
        foreach($prompt as $key => $value) {
            if (!is_array($value)) {
                $prompt[$key] = [
                    'text' => $value,
                    'weight' => 1
                ];
            }
        }
    }
    
    protected function generateFromArray(array $prompt, int $width_ratio, int $height_ratio, string $mime_type, int $seed) : \stdClass {
    
        $data = [
            'modelUri' => $this->model_uri,
            'messages' => $prompt,
            'generationOptions' => [
                'mimeType' => $mime_type,
                'seed' => (string)$seed,
                'aspectRatio' => [
                    'widthRatio' => (string)$width_ratio,
                    'heightRatio' => (string)$height_ratio
                ]
            ]
        ];
        
        $result = $this->post(static::URL_GENERATE, $data);
        
        if ($result['http_code'] != 200 && $result['error']) {
            $error_message = "$result[http_code] - $result[error]";
            throw new \Exception($error_message);
        }
        
        return json_decode($result['response']);
    }
    
    protected function post($url, $data) {
        
        $headers = [
            "Authorization: Bearer $this->api_key",
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
    
    protected function get($url, $params = []) {

        $headers = [
            "Authorization: Bearer $this->api_key",
            "Content-Type: application/json",
        ];

        // Добавляем параметры к URL, если они есть
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
