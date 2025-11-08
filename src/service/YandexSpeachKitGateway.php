<?php

namespace losthost\BlagoBot\service;

class YandexSpeachKitGateway extends YandexAbstractGateway {
 
    public function recognize(string $bytes) : string {
        
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $this->api_key",
            "x-folder-id: $this->folder_id",
            "x-data-logging-enabled: true",
        ];
        $url = "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&folderId={$this->folder_id}&format=oggopus";
        
        $response = $this->post($url, $bytes, $headers);
        $result = json_decode($response["response"]);
        return $result->result;
    }
}
