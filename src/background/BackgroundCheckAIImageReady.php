<?php

namespace losthost\BlagoBot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\BlagoBot\service\YandexARTGateway;
use losthost\telle\Bot;

class BackgroundCheckAIImageReady extends AbstractBackgroundProcess {
    
    #[\Override]
    public function run() {
    
        Bot::$language_code = 'default';
        
        require 'etc/bot_config.php';
        $art = new YandexARTGateway($search_folder_id, $search_key);
        $args = explode(' ', $this->param);
        

        $operation = $art->operationGet($args[1]);
        
        if (isset($operation->error)) {
            $message = <<<FIN
                    При генерации изображения возникла ошибка
                    
                    Код ошибки: <b>{$operation->error->code}</b>
                    Сообщение: <b>{$operation->error->message}</b>
                    Доп. информация:
                    FIN;
                    
            if (empty($operation->error->details)) {
                $message .= ' -';
            } else {
                foreach ($operation->error->details as $description) {
                    $message .= "\n· $description";
                }
            }
            Bot::$api->sendMessage($args[0], $message, 'HTML');
        } elseif ($operation->done) {
            $image = $operation->response->image;
            $tmp_file = tempnam(sys_get_temp_dir(), 'img'). '.jpeg';
            file_put_contents($tmp_file, base64_decode($image));
            
            $file_to_send = new \CURLFile($tmp_file, 'image/jpeg', $tmp_file);
            Bot::$api->sendPhoto($args[0], $file_to_send, $this->getImageCaption());
        } else {
            // Изображение еще не готово
            Bot::$api->sendMessage($args[0], 'Изображение еще не готово.', null);
            Bot::runAt(date_create('+10 seconds'), static::class, $this->param);
        }
    }
    
    protected function getImageCaption() {
        $variants = [
            'Вот изображение, которое вы просили',
            'Создание изображения завершено. Вот результат',
            'Изображение готово. Посмотрите.',
            'Изображение по вашему запросу сгенерировано',
            'Генерация изображения по вашему запросу завершена',
        ];
        
        return $variants[rand(0, count($variants)-1)];
    }
}
