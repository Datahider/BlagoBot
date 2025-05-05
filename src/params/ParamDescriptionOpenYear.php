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
class ParamDescriptionOpenYear extends ParamDescriptionPeriod {
 
    public function getPrompt(): string {
        return "Выберите год открытия";
    }
    
    public function getTitle(): string {
        return "Год открытия";
    }
    
}
