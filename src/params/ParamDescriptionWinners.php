<?php

namespace losthost\BlagoBot\params;

use losthost\DB\DBView;

class ParamDescriptionWinners extends AbstractParamDescription {
    
    protected function initValueSetAndDefauls() {
    
        $sql = <<<FIN
                SELECT DISTINCT
                        contract.x_contragent_id AS contragent_id,
                        contragent.name AS contragent_name
                FROM
                        [x_contract] AS contract
                        LEFT JOIN [x_contragent] AS contragent ON contract.x_contragent_id = contragent.id
                WHERE 
                        contract.x_contragent_id IS NOT NULL 
                        AND contract.status = 'Контракт'
                        AND contragent.name NOT IN ('нд', 'x')
                ORDER BY 
                        contragent.name                
                FIN;
        
        $this->value_set = [];
        $winners = new DBView($sql);
                
        while ($winners->next()) {
            $this->value_set[] = new ParamValue($winners->contragent_name, $winners->contragent_id);
        }
        
        $this->defaults = [];
    }

    public function getName(): string {
        return 'winners';
    }

    public function getPrompt(): string {
        return "Выберите победителей";
    }

    public function getTitle(): string {
        return "Победители";
    }

    public function isMandatory(): bool {
        return true;
    }

    public function isMultipleChoice(): bool {
        return true;
    }
}
