<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionResponsible extends ParamDescriptionResponsibleAll {
    
    protected function initValueSetAndDefauls() {
        parent::initValueSetAndDefauls();
        $this->defaults = [];
    }
}
