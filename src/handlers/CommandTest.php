<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;

use function losthost\BlagoBot\sendSplitMessage;

class CommandTest extends AbstractHandlerCommand {
    
    const COMMAND = 'test';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $text = <<<FIN
            1
            <!-- SPLIT -->
            2
            <!-- SPLIT -->
            3
            <!-- SPLIT -->
            4
            <!-- SPLIT -->
            5
            <!-- SPLIT -->
            6
            <!-- SPLIT -->
            7
            <!-- SPLIT -->
            8
            <!-- SPLIT -->
            9
            <!-- SPLIT -->
            10
            <!-- SPLIT -->
            11
            <!-- SPLIT -->
            12
            <!-- SPLIT -->
            13
            <!-- SPLIT -->
            14
            <!-- SPLIT -->
            15
            <!-- SPLIT -->
            16
            <!-- SPLIT -->
            17
            <!-- SPLIT -->
            18
            <!-- SPLIT -->
            19
            <!-- SPLIT -->
            20
            <!-- SPLIT -->
            FIN;
        
        try {
            sendSplitMessage($message->getChat()->getId(), $text);
        } catch (\Exception $e) {
            Bot::logException($e);
        }
        return true;
    }
}
