<?php

namespace losthost\BlagoBot\service;

use losthost\telle\Bot;
use losthost\BlagoBot\data\log_report;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;

use function \losthost\BlagoBot\sendMessageWithRetry;

class AIFunctionImageSearch extends AIFunction {

    protected string $search_key;
    protected string $search_url;
    protected string $search_folder_id;
    
    public function __construct() {

        require 'etc/bot_config.php';
        
        $this->search_key = $search_key;
        $this->search_url = 'https://searchapi.api.cloud.yandex.net/v2/image/search';
        $this->search_folder_id = $search_folder_id;
        
    }
    
    #[\Override]
    public function getResult(array $params): mixed {

        global $b_user;
        
        sendMessageWithRetry(Bot::$chat->id, "Ищу изображения в интернете по запросу: «$params[prompt]»...", null);
        
        $log = log_report::log_start($b_user->id, static::class);
        
        $result = $this->search($params['prompt']);
        
        $log->log_stop();
        
        $count = $this->downloadAndSend($this->getXMLObject($result));
        
        return "Готово. Скажи пользователю, что ты отправил ему $count изображений по его запросу. Не уточняй куда, т.к. изображения уже пришли в этот же чат.";
    }
    
    protected function getXMLObject(string $input) : \SimpleXMLElement {
        $decoded = base64_decode($input, true);

        // Проверим: получилось ли что-то похоже на XML
        if ($decoded !== false && strpos($decoded, '<') !== false) {
            $xmlString = $decoded;
        } else {
            $xmlString = $input; // значит, это уже нормальный XML
        }

        $xml = simplexml_load_string($xmlString);
        return $xml;
    }
    
    protected function downloadAndSend(\SimpleXMLElement $xml) {

        $files = $this->downloadImages($this->getImageUrls($xml));
        return $this->sendImages($files);
        
    }
    
    protected function sendImages(array $files) : int {
        $chat_id = Bot::$chat->id;
        $media_group = [];
        $attachments = [];

        $i = 0;
        foreach ($files as $file) {
            $mime_type = $file['mime'];
            $type = strpos($mime_type, 'image/') === 0 ? 'photo' : 'document';

            $media_group[] = InputMediaPhoto::fromResponse([
                'type' => $type,
                //'media' => new \CURLFile($file['path'], $mime_type, basename($file['path'])),
                'media' => "attach://file$i",
                //'caption' => "Файл #$index", // если нужно
            ]);
            $attachments["file$i"] = new \CURLFile($file['path'], $mime_type, basename($file['path']));
            $i++;
        }

        // Отправка
        $bot_api = Bot::$api;
        $media = new ArrayOfInputMedia($media_group);
        $bot_api->sendMediaGroup($chat_id, $media, false, null, null, null, null, $attachments);
        
        return count($files);
    }
    
    protected function downloadImages(array $urls) : array {
    
        $tmp_dir = sys_get_temp_dir();
        $max_files = 4;
        $files = [];
        
        foreach ($urls as $url) {
            if (count($files) >= $max_files) break;

            $file = $this->downloadFile($url, $tmp_dir);
            if ($file) { $files[] = $file; }
        }
        
        return $files;
    }
    
    protected function downloadFile($url, $tmp_dir) {

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CAINFO => 'vendor/losthost/telle/src/cacert.pem',  
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);
            return false;
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body        = substr($response, $header_size);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($http_code !== 200 || !$body) {
            return false;
        }

        $mime_map = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        $ext = $mime_map[$content_type] ?? 'bin';
        $filename = tempnam($tmp_dir, "tg_");
        $new_path = $filename . "." . $ext;

        unlink($filename);
        file_put_contents($new_path, $body);

        return [
            'path' => $new_path,
            'mime' => $content_type,
        ];
    }


    protected function getImageUrls(\SimpleXMLElement $xml) : array {
        $urls = [];
        foreach ($xml->response->results->grouping->group as $group) {
            $urls[] = (string) $group->doc->url;
        }
        
        return $urls;
    }
    
    protected function search(string $prompt) : string {
        
        $data = [
            'query' => [
                'searchType' => 'SEARCH_TYPE_RU',
                'queryText' => $prompt,
                'page' => 1,
            ],
            'docsOnPage' => 20,
            'folderId' => $this->search_folder_id,
        ];
        
        $result = $this->post($this->search_url, $data);
        
        if ($result['response'] === false) {
            return "Не удалось получить результат поиска из за ошибки связи.";
        }
        
        if ($result['http_code'] !== 200) {
            return "Не удалось получить результат поиска из-за ошибки на поисковом сервере.";
        }
        
        $decoded = json_decode($result['response'], true);  
        
        return $decoded['rawData'];
        
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
