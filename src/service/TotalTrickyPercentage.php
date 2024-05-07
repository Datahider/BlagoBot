<?php

namespace losthost\BlagoBot\service;

class TotalTrickyPercentage extends AbstractTotalsCollector {
    
    public function calculate(int $index, mixed $value = null) {

        $total_limit = $this->args[0]->getTotals($index);
        
        $total_value = empty($this->args[1]) ? 0 : $this->args[1]->getTotals($index);
        $total_value += empty($this->args[2]) ? 0 : $this->args[2]->getTotals($index);
        
        return $total_value / $total_limit;
    }
}
