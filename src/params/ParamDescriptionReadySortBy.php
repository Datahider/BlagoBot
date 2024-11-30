<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionReadySortBy extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $percent = new ParamValue('По готовности (убыв.)', 'ready_percent#desc');
        $this->value_set = [
            $percent,
            new ParamValue('По плановой дате открытия', 'open_date_planned'),
            new ParamValue('По фактической дате открытия', 'open_date_fact'),
            new ParamValue('По мероприятию', 'category'),
            new ParamValue('По ответственному', 'responsible'),
            new ParamValue('По ОМСУ', 'omsu'),
        ];
        $this->defaults = [$percent];
    }

    public function getName(): string {
        return 'sortby';
    }

    public function getPrompt(): string {
        return 'Выберите сортировку';
    }

    public function getTitle(): string {
        return 'Сортировка';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return false;
    }
}
