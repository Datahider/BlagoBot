<?php

namespace losthost\BlagoBot\params;

use losthost\DB\DBView;

class ParamDescriptionOmsu extends AbstractParamDescription {
    
    public function getPrompt(): string {
        return 'Выберите ОМСУ';
    }

    public function getTitle(): string {
        return 'ОМСУ';
    }
    
    public function getName() : string {
        return 'omsu';
    }

    protected function initValueSetAndDefauls() {
        $omsus = [];
        $omsu = new DBView('SELECT id, name FROM [x_omsu] ORDER BY name');
        while ($omsu->next()) {
            $omsus[] = new ParamValue($omsu->name, $omsu->id);
        }
        
        $this->value_set = $omsus;
        $this->defaults = [];
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
