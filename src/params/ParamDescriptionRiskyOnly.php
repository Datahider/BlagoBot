<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionRiskyOnly extends AbstractParamDescription {
    //put your code here
    protected function initValueSetAndDefauls() {
        $yes = new ParamValue('Да', 1);
        $no = new ParamValue('Нет', 0);
        
        $this->value_set = [ $yes, $no ];
        $this->defaults = [$no];
    }

    public function getName(): string {
        return 'risky_only';
    }

    public function getPrompt(): string {
        return 'Отобрать только рисковые объекты?';
    }

    public function getTitle(): string {
        return 'Только рисковые';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return false;
    }
}
