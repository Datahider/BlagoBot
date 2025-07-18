<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionOPZStatus extends AbstractParamDescription {
    
    #[\Override]
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue("На подписании", '<'),
            new ParamValue("ОПЗ не наступило", '>'),
            //new ParamValue("Все", 'both')
        ];
        
        $this->defaults = $this->value_set;
    }

    #[\Override]
    public function getName(): string {
        return "filter";
    }

    #[\Override]
    public function getPrompt(): string {
        return "Выберите что показывать";
    }

    #[\Override]
    public function getTitle(): string {
        return "Фильтра";
    }

    #[\Override]
    public function isMandatory(): bool {
        return true;
    }

    #[\Override]
    public function isMultipleChoice(): bool {
        return true;
    }
}
