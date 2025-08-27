<?php
namespace losthost\BlagoBot\service;

abstract class AIFunction {
    
    abstract public function getResult(array $params) : mixed;
}
