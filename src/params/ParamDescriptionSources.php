<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionSources extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue('Федеральный бюджет', 'fb'),
            new ParamValue('Бюджет МО', 'bmo'),
            new ParamValue('Бюджет Москвы', 'bm'),
            new ParamValue('Бюджет ОМСУ', 'omsu'),
            new ParamValue('Доп. бюджет ОМСУ', 'omsu2'),
        ];
        $this->defaults = [];
    }

    public function getName(): string {
        return 'sources';
    }

    public function getPrompt(): string {
        return 'Выберите источники финансирования';
    }

    public function getTitle(): string {
        return 'Бюджеты';
    }

    public function isMandatory(): bool {
        return false;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
