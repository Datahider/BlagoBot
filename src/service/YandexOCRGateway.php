<?php

namespace losthost\BlagoBot\service;

class YandexOCRGateway extends YandexAbstractGateway {
    
    const MIME_TYPE_JPEG = 'image/jpeg';
    const MIME_TYPE_PDF = 'application/pdf';
    
    const MODEL_PAGE = 'page';
    const MODEL_HANDWRITTEN = 'handwritten';
    const MODEL_TABLE = 'table';
    
    public function __construct(string $folder_id, string $api_key) {
        parent::__construct($folder_id, $api_key);
    }
    
    public function recognizeTextAsync(string $bytes, string $model, string $mime_type, array $language_codes=['*']) {
        
        $data = [
            'mimeType' => $mime_type,
            'languageCodes' => $language_codes,
            'model' => $model,
            'content' => base64_encode($bytes)
        ];
        
        $result = $this->postJson('https://ocr.api.cloud.yandex.net/ocr/v1/recognizeTextAsync', $data, [
            "Content-Type: application/json",
            "Authorization: Bearer $this->api_key",
            "x-folder-id: $this->folder_id",
            "x-data-logging-enabled: true",
        ]);
        
        if ($result['error']) {
            throw new \Exception($result['error']);
        }
        
        return json_decode($result['response']);
    }
    
    public function recognizeText(string $bytes, string $model, string $mime_type, array $language_codes=['*']) {

        $data = [
            'mimeType' => $mime_type,
            'languageCodes' => $language_codes,
            'model' => $model,
            'content' => base64_encode($bytes)
        ];
        
        $result = $this->postJson('https://ocr.api.cloud.yandex.net/ocr/v1/recognizeText', $data, [
            "Content-Type: application/json",
            "Authorization: Bearer $this->api_key",
            "x-folder-id: $this->folder_id",
            "x-data-logging-enabled: true",
        ]);
        
        if ($result['error']) {
            throw new \Exception($result['error']);
        }
        
        return json_decode($result['response']);
    }
    
    public function getRecognition(string $operation_id, bool $wait=false) {
    
        while (true) {
            $result = $this->get('https://ocr.api.cloud.yandex.net/ocr/v1/getRecognition', ['operationId' => $operation_id], [
                "Authorization: Bearer $this->api_key"
            ]);
            
            if ($result['error']) {
                throw new \Exception($result['error']);
            }
            
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $result['response']);
            rewind($stream);
            
            $text = '';
            while (($line = fgets($stream)) !== false) {
                // Декодируем JSON из строки
                $obj = json_decode($line);

                if ($obj->error) {
                    continue 2; 
                }
    
                $this->collectTextFields($obj, $text);
                $text .= "\n";
            }

            // Закрываем файл
            fclose($stream);
            
            return $text;
        }
    }
    
    protected function collectTextFields($obj, &$result = '') {
        // Проверяем каждый элемент объекта
        foreach ($obj as $key => $value) {
            // Если нашли поле 'text' - добавляем его значение к результату
            if ($key === 'text') {
                $result .= (string)$value. "\n";
            }

            // Если значение является объектом - рекурсивно обрабатываем его
            if (is_object($value)) {
                $this->collectTextFields($value, $result);
            }

            // Если значение является массивом - обрабатываем каждый элемент
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (is_object($item)) {
                        $this->collectTextFields($item, $result);
                    }
                }
            }
        }
    }


}
