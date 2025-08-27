<?php

namespace losthost\BlagoBot\service;

class AIFunctionVersion extends AIFunction {
    
    #[\Override]
    public function getResult(array $params): mixed {
        return '0.0.52';
    }
}
