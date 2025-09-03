<?php

namespace losthost\BlagoBot\service;

use losthost\telle\Bot;
use losthost\BlagoBot\service\TableMap;
use losthost\BlagoBot\data\user;

use function \losthost\BlagoBot\sendMessageWithRetry;

class AIFunctionSendFileToUser extends AIFunction {
    
    #[\Override]
    public function getResult(array $params): mixed {
        
        global $b_user;
        
        sendMessageWithRetry(Bot::$chat->id, 'Отправляю файл...', null);
        
        $user_map = new TableMap('user_view', 'id', 'full_name');        
        $user_id = $user_map->keyByValue($params['user']);
        $user = new user(['id' => $user_id], true);
        if ($user->isNew()) {
            return "Не удалось отправить файл, так как не найден такой пользователь.";
        }
        
        
        $caption = "$b_user->surname $b_user->name $b_user->fathers_name отправил вам файл с комментарием:\n\n$params[text]";
        try {
            $result = Bot::$api->sendDocument($user->tg_user, $params['file_id'], $caption);
        } catch (\Exception $exc) {
            return "При отправке файла возникла ошибка: ". $exc->getMessage();
        }
        
        if (!$result) {
            return "Отправить файл не удалось.";
        }
        
        return "Пользователю $params[user] был отправлен файл с идентификатором '$params[file_id]' с сопроводительным текстом: «$params[text]»";
        
    }
}
