<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionPeriod extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue(2025),
            new ParamValue(2026),
            new ParamValue(2027),
            new ParamValue(2028),
            new ParamValue(2029),
        ];
        $this->defaults = [];
    }

    public function getName(): string {
        return 'year';
    }

    public function getPrompt(): string {
        return 'Выберите период';
    }

    public function getTitle(): string {
        return 'Период';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
