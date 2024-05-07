<?php

namespace losthost\BlagoBot\service;

abstract class AbstractTotalsCollector {
    
    protected $args;
    
    public function __construct(array $args=[]) {
        $this->args = $args;
    }
    
    abstract public function calculate(int $index, mixed $value=null);
}
