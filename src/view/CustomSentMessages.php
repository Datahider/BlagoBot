<?php

namespace losthost\BlagoBot\view;

use losthost\telle\Bot;

use function \losthost\BlagoBot\sendSplitMessage;

class CustomSentMessages extends AbstractCustomView {
    
    public function show() {

        if ($this->result->params['selfcopy'][0] == 111) {
            foreach ($this->result->data as $elem) {
                sendSplitMessage(Bot::$chat->id, $elem[3]);
            }
        }
        
        $report_text = '';
        foreach ($this->result->data as $line) {
            foreach ($this->result->columns as $key => $column) {
                $report_text .= "$column: $line[$key]\n";
            }
            $report_text .= "\n";
        }
        sendSplitMessage(Bot::$chat->id, $report_text);
    }
}
