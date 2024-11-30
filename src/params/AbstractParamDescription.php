<?php

namespace losthost\BlagoBot\params;

use function \losthost\BlagoBot\getClassIndex;

abstract class AbstractParamDescription {

    protected array $value_set;
    protected array $defaults;
    protected string $report_class;


    public function __construct($report_class) {
        if (is_numeric($report_class)) {
            $this->report_class = getClassIndex($report_class);
        } elseif (is_string($report_class)) {
            $this->report_class = $report_class;
        } else {
            $this->report_class = get_class($report_class);
        }
        
        $this->initValueSetAndDefauls();
    }

    abstract protected function initValueSetAndDefauls();

    abstract public function getName() : string;
    abstract public function getTitle() : string;
    abstract public function getPrompt() : string;
    abstract public function isMandatory() : bool;
    abstract public function isMultipleChoice() : bool;


    public function getValueSet() : array {
        return $this->value_set;
    }
    
    public function getDefaults() : array {
        return $this->defaults;
    }
    
    public function getDefaultValues() : array {
        $result = [];
        foreach ($this->defaults as $default) {
            $result[] = $default->getValue();
        }
        return $result;
    }
    
    public function valueByValue(string|int $value) : ParamValue {
        foreach ($this->value_set as $value_from_set) {
            if ($value_from_set->getValue() == $value) {
                return $value_from_set; 
            } 
        }
        return null;
    }
    
    public function getReportClass() {
        return $this->report_class;
    }
}
