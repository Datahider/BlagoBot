<?php

namespace losthost\BlagoBot\service;

class YandexOCRGateway extends YandexAbstractGateway {
    
    const MIME_TYPE_JPEG = 'image/jpeg';
    const MIME_TYPE_PDF = 'application/pdf';
    
    const MODEL_PAGE = 'page';
    const MODEL_HANDWRITTEN = 'handwritten';
    const MODEL_TABLE = 'table';
    
    #[\Override]
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
        
        $result = $this->postJson('https://ocr.api.cloud.yandex.net/ocr/v1/recognizeTextAsync', $data);
        
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
        
        $result = $this->postJson('https://ocr.api.cloud.yandex.net/ocr/v1/recognizeTextAsync', $data);
        
        if ($result['error']) {
            throw new \Exception($result['error']);
        }
        
        return json_decode($result['response']);
    }
    
    public function getRecognition(string $operation_id) {
        
    }
}
