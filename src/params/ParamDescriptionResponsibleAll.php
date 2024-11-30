<?php

namespace losthost\BlagoBot\params;

use losthost\DB\DBView;

class ParamDescriptionResponsibleAll extends AbstractParamDescription {
    //put your code here
    protected function initValueSetAndDefauls() {
        $responsibles = [];
        $responsible = new DBView('SELECT r.id, u.surname, u.name, u.fathers_name FROM [x_responsible] AS r INNER JOIN [user] AS u ON r.user_id = u.id ORDER BY u.surname, u.name, u.fathers_name');
        while ($responsible->next()) {
            $responsibles[] = new ParamValue(sprintf('%s %s.%s.', $responsible->surname, mb_substr($responsible->name, 0, 1), mb_substr($responsible->fathers_name, 0, 1)), $responsible->id);
        }
        
        $this->value_set = $responsibles;
        $this->defaults = $responsibles;
    }

    public function getName(): string {
        return 'responsible';
    }

    public function getPrompt(): string {
        return 'Выберите ответственных';
    }

    public function getTitle(): string {
        return 'Ответственные';
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
