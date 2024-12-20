<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionRiskyOnly extends AbstractParamDescription {
    //put your code here
    protected function initValueSetAndDefauls() {
        $all_risky = new ParamValue('Все рисковые', 3);
        $part_open = new ParamValue('Открытые, СГ<100%', 2);
        $not_open = new ParamValue('Просрочена дата открытия', 1);
        $no = new ParamValue('Нет (Все объекты)', 0);
        
        $this->value_set = [ $all_risky, $part_open, $not_open, $no ];
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
