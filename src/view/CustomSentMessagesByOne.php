<?php

namespace losthost\BlagoBot\view;

use losthost\telle\Bot;

use function \losthost\BlagoBot\sendSplitMessage;

class CustomSentMessagesByOne extends AbstractCustomView {
    
    public function show() {

        if ($this->result->params['selfcopy'][0] == 111) {
            foreach ($this->result->data as $elem) {
                sendSplitMessage(Bot::$chat->id, $elem[3]);
            }
        }
        
        $report_data = [];
        
        foreach ($this->result->data as $line) {
            $message_text = '';
            foreach ($this->result->columns as $key => $column) {
                $message_text .= "$column: <b>$line[$key]</b>\n";
            }
            $report_data[] = $message_text;
        }
        $report_text = str_replace("\n", '<!-- SPLIT -->', $report_text);
        sendSplitMessage(Bot::$chat->id, implode('<!-- SPLIT -->', $report_data));
    }
}
