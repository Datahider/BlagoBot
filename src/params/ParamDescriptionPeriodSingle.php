<?php

namespace losthost\BlagoBot\params;

class ParamDescriptionPeriodSingle extends ParamDescriptionPeriod {
    
    public function isMultipleChoice(): bool {
        return false;
    }
}
