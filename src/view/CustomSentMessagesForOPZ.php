<?php

namespace losthost\BlagoBot\view;

use losthost\telle\Bot;

use function \losthost\BlagoBot\sendSplitMessage;

class CustomSentMessagesForOPZ extends AbstractCustomView {
    
    public function show() {

        $report_data = [];
        $messages_total = 0;
        $messages_on_signing = 0;
        $messages_in_future = 0;
        
        foreach ($this->result->data as $line) {
            $message_text = '';
            $messages_total++;
            if ($line[8]) {
                $messages_on_signing++;
            } else {
                $messages_in_future++;
            }
            
            foreach ($this->result->columns as $key => $column) {
                $message_text .= "$column: <b>$line[$key]</b>\n";
            }
            $report_data[] = $message_text;
        }

        if (count($this->result->params['filter']) == 2) { 
            $messages = <<<FIN
                    Всего закупок: <b>$messages_total</b>
                    Из них:
                    • На подписании: <b>$messages_on_signing</b>
                    • ОПЗ не наступило: <b>{$messages_in_future}</b>
                    <!-- SPLIT -->
                    FIN. implode('<!-- SPLIT -->', $report_data);
        } elseif ($this->result->params['filter'][0] == '<') {
            $messages = <<<FIN
                    Всего закупок: <b>$messages_total</b>
                    Из них:
                    • На подписании: <b>$messages_on_signing</b>
                    <!-- SPLIT -->
                    FIN. implode('<!-- SPLIT -->', $report_data);
        } else {
            $messages = <<<FIN
                    Всего закупок: <b>$messages_total</b>
                    Из них:
                    • ОПЗ не наступило: <b>{$messages_in_future}</b>
                    <!-- SPLIT -->
                    FIN. implode('<!-- SPLIT -->', $report_data);
        }
        sendSplitMessage(Bot::$chat->id, $messages);
    }
}
