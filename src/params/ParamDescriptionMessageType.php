<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionMessageType extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue('Общий статус реализации программы', 88),
            new ParamValue('Риски по срокам', 89)
        ];
        $this->defaults = [];
    }

    public function getName(): string {
        return 'msgtype';
    }

    public function getPrompt(): string {
        return 'Выберите тип сообщения';
    }

    public function getTitle(): string {
        return 'Тип сообщения';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return false;
    }
}
