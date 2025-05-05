<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\BlagoBot\params;

/**
 * Description of ParamDescriptionOpenYear
 *
 * @author web
 */
class ParamDescriptionOpenYear extends AbstractParamDescription {
 
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

    public function getPrompt(): string {
        return "Выберите год открытия";
    }
    
    public function getTitle(): string {
        return "Год открытия";
    }
    
    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }

    public function getName(): string {
        return 'openyear';
    }
}
