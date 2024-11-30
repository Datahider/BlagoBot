<?php

namespace losthost\BlagoBot\service;

class TotalNulls extends AbstractTotalsCollector {
    
    public function calculate(int $index, mixed $value = null) {
        return $this->args[0]->getTotals($index) - $this->args[1]->getTotals($index);
    }
}
