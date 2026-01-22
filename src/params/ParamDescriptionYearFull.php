<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionYearFull extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue(2019),
            new ParamValue(2020),
            new ParamValue(2021),
            new ParamValue(2022),
            new ParamValue(2023),
            new ParamValue(2024),
            new ParamValue(2025),
            new ParamValue(2026),
            new ParamValue(2027),
            new ParamValue(2028),
            new ParamValue(2029),
            new ParamValue(2030),
        ];
        $this->defaults = [];
    }

    public function getName(): string {
        return 'certyears';
    }

    public function getPrompt(): string {
        return 'Выберите годы';
    }

    public function getTitle(): string {
        return 'Годы';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
