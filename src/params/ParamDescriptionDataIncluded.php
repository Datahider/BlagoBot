<?php
namespace losthost\BlagoBot\params;

class ParamDescriptionDataIncluded extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [
            new ParamValue('Разбивка по бюджетам', 15),
            new ParamValue('Опубликовано', 16),
            new ParamValue('Законтрактовано', 17),
            new ParamValue('Подано заявок на оплату', 18),
            new ParamValue('Оплачено', 19),
            new ParamValue('Остаток по итогам торгов', 20),
            new ParamValue('Остаток по итогам освоения', 21),
        ];
        
        $this->defaults = $this->value_set;
        array_splice($this->defaults, 0, 1);
    }

    public function getName(): string {
        return 'data';
    }

    public function getPrompt(): string {
        return 'Отметьте данные для включения в отчет';
    }

    public function getTitle(): string {
        return 'Включаемые данные';
    }

    public function isMandatory(): bool {
        return false;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
