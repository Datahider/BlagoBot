<?php

namespace losthost\BlagoBot\service;

/**
 * Description of TotalCount
 *
 * @author web
 */
class TotalCount extends AbstractTotalsCollector {
    
    public function calculate(int $index, mixed $value = null) {
        return $this->args[0]->getTotals($index) + ($value ? 1 : 0);
    }
}
