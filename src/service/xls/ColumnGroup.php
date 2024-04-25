<?php

namespace losthost\BlagoBot\service\xls;

class ColumnGroup {
    
    protected string $title;
    protected array $header_format;
    
    public function __construct(string $title, array $header_format) {
        $this->title = $title;
        $this->header_format = $header_format;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getHeaderFormat() {
        return $this->header_format;
    }
    
}
