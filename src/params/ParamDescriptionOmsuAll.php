<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionOmsuAll extends ParamDescriptionOmsu {
    
    protected function initValueSetAndDefauls() {
        parent::initValueSetAndDefauls();
        $this->defaults = $this->value_set;
    }
}
