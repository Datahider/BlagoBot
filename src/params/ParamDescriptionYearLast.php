<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionYearLast extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue(2024),
            new ParamValue(2025),
            new ParamValue(2026),
            new ParamValue(2027),
            new ParamValue(2028),
            new ParamValue(2029),
        ];
        $this->defaults = [];
    }

    public function getName(): string {
        return 'gpyears';
    }

    public function getPrompt(): string {
        return 'Выберите годы реализации';
    }

    public function getTitle(): string {
        return 'Годы реализации';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
