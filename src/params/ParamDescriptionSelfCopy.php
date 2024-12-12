<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionSelfCopy extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue('Отправлять', 111),
            new ParamValue('Не отправлять', 112)
        ];
        $this->defaults = $this->value_set;
        array_splice($this->defaults, 0, 1);
    }

    public function getName(): string {
        return 'selfcopy';
    }

    public function getPrompt(): string {
        return 'Режим отправки копии себе';
    }

    public function getTitle(): string {
        return 'Копия себе';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return false;
    }
}
