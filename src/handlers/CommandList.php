<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class CommandList extends AbstractHandlerCommand {

    const COMMAND = 'list';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $data = $this->prepareData();
        $messages = $this->prepareMessages($data);
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        foreach ($messages as $text) {
            $view->show('tpl_info', null, ['type' => 'custom', 'text' => $text]);
        }
        
        return true;
    }
    
    protected function prepareData() : array {
        return [
            'linked' => $this->getLinked(),
            'other' => $this->getOthers() 
        ];
    }
    
    protected function prepareMessages(array $data) : array {
        return [
            'Сообщение 1',
            "Сообщение №2"
        ];
    }
}
