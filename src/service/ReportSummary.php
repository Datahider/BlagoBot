<?php

namespace losthost\BlagoBot\service;

use DateTimeImmutable;

class ReportSummary {

    protected string $title;
    protected DateTimeImmutable $date_generated;
    protected array $params; // ['title' => 'Some title', 'value' => 'The value(s)'];
    protected string $note;
    
    public function __construct(string $title, DateTimeImmutable $date_generated, array $params, string $note='') {
        $this->title = $title;
        $this->date_generated = $date_generated;
        $this->params = $params;
        $this->note = $note; 
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getDateGenerated() {
        return $this->date_generated;
    }
    
    public function getParams() {
        return $this->params;
    }
    
    public function getNote() {
        return $this->note;
    }
}
