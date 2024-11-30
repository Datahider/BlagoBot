<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionReadyGroupBy extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $none = new ParamValue('Без группировки', 'none');
        $this->value_set = [
            $none,
            new ParamValue('По году', 'year'),
            new ParamValue('По мероприятию', 'category'),
            new ParamValue('По ответственному', 'responsible'),
            new ParamValue('По ОМСУ', 'omsu'),
        ];
        $this->defaults = [$none];
    }

    public function getName(): string {
        return 'groupby';
    }

    public function getPrompt(): string {
        return 'Выберите группировку';
    }

    public function getTitle(): string {
        return 'Группировка';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return false;
    }
}
