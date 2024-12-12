<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionAddressee extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue('Глава', 86),
            new ParamValue('Зам. главы', 87)
        ];
        $this->defaults = [];
    }

    public function getName(): string {
        return 'recipient';
    }

    public function getPrompt(): string {
        return 'Выберите получателя рассылки';
    }

    public function getTitle(): string {
        return 'Адресат';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return false;
    }
}
