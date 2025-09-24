<?php

namespace losthost\BlagoBot\service;

class TotalPercentage extends AbstractTotalsCollector {
    
    public function calculate(int $index, mixed $value = null) {
        
        $column2 = $this->args[1];
        $column1 = $this->args[0];
        
        if ($column1->getTotals($index) == 0) {
            return "-";
        } else {
            return $column2->getTotals($index) / $column1->getTotals($index);
        }
    }
}
