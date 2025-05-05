<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionCompletion extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue(2025),
            new ParamValue(2026),
            new ParamValue(2027),
            new ParamValue(2028),
        ];
        $this->defaults = [];
    }

    public function getName(): string {
        return 'completion';
    }

    public function getPrompt(): string {
        return 'Выберите срок завершения';
    }

    public function getTitle(): string {
        return 'Срок завершения';
    }

    public function isMandatory(): bool {
        return false;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
