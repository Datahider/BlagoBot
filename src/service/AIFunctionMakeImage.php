<?php

namespace losthost\BlagoBot\service;

use losthost\telle\Bot;
use losthost\BlagoBot\service\YandexARTGateway;
use losthost\BlagoBot\background\BackgroundCheckAIImageReady;

class AIFunctionMakeImage extends AIFunction {
    
    #[\Override]
    public function getResult(array $params): mixed {

        require 'etc/bot_config.php';
        
        $art = new YandexARTGateway($search_folder_id, $search_key);
        
        if (isset($params['orientation']) && $params['orientation'] == 'horizontal') {
            $width = 2;
            $height = 1;
        } elseif (isset($params['orientation']) && $params['orientation'] == 'vertical') {
            $width = 1;
            $height = 2;
        } else {
            $width = 1;
            $height = 1;
        }
        
        $result = $art->generate($params['prompt'], $width, $height);
        
        if (isset($result->error)) {
            return "При создании запроса на генерацию изображения по запросу <b>«$params[prompt]»</b> возникла ошибка: $result->error";
        }
        
        Bot::runAt(date_create('+10 seconds'), BackgroundCheckAIImageReady::class, Bot::$chat->id. ' '. $result->id);
        
        $text = <<<FIN
            Начато создание изображения по следующему описанию:
            
            «$params[prompt]»
            FIN;
        
        Bot::$api->sendMessage(Bot::$chat->id, $text, null);
        
        return <<<FIN
            Запрос на создание изображения выполнен. Изображение будет отправлено пользователю как только будет готово. 
            А пока ты можешь продолжить диалог с пользователем. 
            Если пользователь ответит кратко, без явного запроса на новую генерацию, скажи что рад помочь или что-то этом духе.
            FIN;
    }
}
