<?php

namespace losthost\BlagoBot\params;

class ParamValue {

    protected string|int $value;
    protected string $title;
    
    public function __construct(string $title, string|int|null $value=null) {
        $this->title = $title;
        $this->value = is_null($value) ? $title : $value;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getValue() {
        return $this->value;
    }
    
    public function getId() {
        return $this->getValue();
    }
}
