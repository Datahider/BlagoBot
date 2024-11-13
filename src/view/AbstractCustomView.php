<?php

namespace losthost\BlagoBot\view;

abstract class AbstractCustomView {
    
    protected \stdClass $result;
    
    public function __construct(\stdClass $result) {
        $this->result = $result;
    }
    
    abstract public function show();
}
