<?php

namespace losthost\BlagoBot\service;

class TotalPercentage extends AbstractTotalsCollector {
    
    public function calculate(int $index, mixed $value = null) {
        
        $column2 = $this->args[1];
        $column1 = $this->args[0];
        
        return $column2->getTotals($index) / $column1->getTotals($index);
    }
}
