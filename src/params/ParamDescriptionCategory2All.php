<?php

namespace losthost\BlagoBot\params;

use losthost\DB\DBView;

class ParamDescriptionCategory2All extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [];
        $category = new DBView('SELECT DISTINCT category2_name AS name FROM [x_object] ORDER BY name');
        
        $index = 0;
        while ($category->next()) {
            $this->value_set[] = new ParamValue($category->name, $index);
            $index++;
        }
        
        $this->defaults = $this->value_set;
    }

    public function getName(): string {
        return 'cat2';
    }

    public function getPrompt(): string {
        return 'Выберите мероприятия';
    }

    public function getTitle(): string {
        return 'Мероприятия';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
