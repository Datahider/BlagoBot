<?php

namespace losthost\BlagoBot\service;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;

use losthost\telle\model\DBBotParam;
use losthost\DB\DB;

class IAMToken {
    
    const RENEW_AFTER = 3600;
    const EXPIRES_AFTER = 7200;
    
    public function get() {
        
        $now = time();
        
        $token_created = new DBBotParam('iam_token_created');
        $token_data = new DBBotParam('iam_token_data');
        
        if (!$token_created->value || $token_created->value < $now-self::RENEW_AFTER) {
            $token_data->value = $this->makeNew();
            $token_created->value = $now;
        }
        
        return $token_data->value;
    }
    
    protected function makeNew() {
        
        $key_data = json_decode(file_get_contents('etc/authorized_key.json'), true);

        if ($key_data === false) {
            throw new \Exception('Failed to read authorized_key file.');
        }

        $private_key = $key_data['private_key'];
        $key_id = $key_data['id'];
        $service_account_id = $key_data['service_account_id'];

        // Необходимо удалить заголовок/метаданные из закрытого ключа
        if (strpos($private_key, "PLEASE DO NOT REMOVE THIS LINE!") === 0) {
            $private_key = substr($private_key, strpos($private_key, "\n") + 1);
        }        
        
        // 3. Формируем payload (данные для JWT)
        $now = time();
        $payload = json_encode([
            'aud' => 'https://iam.api.cloud.yandex.net/iam/v1/tokens',
            'iss' => $service_account_id,
            'iat' => $now,
            'exp' => $now + 3600, // Токен живёт 1 час
        ]);

        // 4. Создаем JWK (JSON Web Key) из вашего приватного ключа
        //    Второй параметр - пароль для ключа (обычно null)
        $jwk = JWKFactory::createFromKey($private_key, null, [
            'kid' => $key_id, // Указываем ID ключа
            'alg' => 'PS256', // Указываем алгоритм
            'use' => 'sig' // Указываем, что ключ для подписи (signature)
        ]);

        // 5. Настраиваем менеджер алгоритмов и добавляем PS256
        $algorithm_manager = new AlgorithmManager([
            new PS256()
        ]);

        // 6. Создаем подписанный JWT (JWS)
        $jws_builder = new JWSBuilder($algorithm_manager);
        $jws = $jws_builder
            ->create() // Создаем новый JWS
            ->withPayload($payload) // Добавляем payload
            ->addSignature($jwk, [ // Добавляем подпись с нашим ключом
                'kid' => $key_id, // Указываем ID ключа в заголовках
                'alg' => 'PS256' // Указываем алгоритм в заголовках
            ])
            ->build(); // Собираем

        // 7. Сериализуем JWS в компактную форму (строка, разделенная точками)
        $serializer = new CompactSerializer();
        $jwt = $serializer->serialize($jws, 0); // 0 - индекс первой (и в нашем случае единственной) подписи

        // 8. Формируем и отправляем запрос к IAM API для обмена JWT на IAM-токен
        $url = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';
        $ch = curl_init();

        $post_data = ['jwt' => $jwt];
        $post_data_json = json_encode($post_data);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data_json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_data_json)
            ],
            CURLOPT_CAINFO => 'vendor/losthost/telle/src/cacert.pem'
        ]);

        // 9. Отправляем запрос и получаем ответ
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('CURL Error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($http_code !== 200) {
            throw new \Exception('Ошибка при получении IAM-токена. HTTP Code: ' . $http_code . '. Response: ' . $response);
        }

        // 10. Извлекаем IAM-токен из ответа
        $response_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Ошибка декодирования JSON ответа: ' . json_last_error_msg());
        }

        $iam_token = $response_data['iamToken'];
        return $iam_token;
    }
}
