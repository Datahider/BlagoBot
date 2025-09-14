<?php

namespace losthost\BlagoBot\service;

abstract class YandexAbstractGateway {
    
    protected string $folder_id;
    protected string $api_key;
    protected string $model_uri;
    
    public function __construct(string $folder_id, string $api_key) {
        $this->folder_id = $folder_id;
        $this->api_key = $api_key;
    }
    
    protected function get($url, $params=[], $headers=[]) {

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
    
    protected function postJson($url, $data, $headers=[]) {

        $json = json_encode($data);
        
        return $this->post($url, $json, $headers);
    }
    
    protected function post($url, $data, $headers=[]) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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
