<?php

namespace losthost\BlagoBot\params;

use losthost\DB\DBView;

class ParamDescriptionCategory extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
        $this->value_set = [];
        $category = new DBView('SELECT id, name FROM [x_category] ORDER BY name');
        
        while ($category->next()) {
            $this->value_set[] = new ParamValue($category->name, $category->id);
        }
        
        $this->defaults = [];
    }

    public function getName(): string {
        return 'activity';
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
