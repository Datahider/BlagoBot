<?php

namespace losthost\BlagoBot\service;

class AIToolCaller {
    
    public function getToolResults(array $tool_calls) {
        
        $result = [];
        
        foreach ($tool_calls as $tool_call) {
            if (isset($tool_call['functionCall'])) {
                $name = $tool_call['functionCall']['name'];
                $params = $tool_call['functionCall']['arguments'];
                $class = $this->getFunctionClass($name);
                
                $function = new $class();
                
                $function_result = $function->getResult($params);
                $result[] = [
                    'functionResult' => [
                        'name' => $name,
                        'content' => $function_result
                    ]
                ];
            }
        }
        
        return $result;
    }
    
    protected function getFunctionClass($function_name) {
        return 'losthost\\BlagoBot\\service\\AIFunction'. $this->snakeToPascal($function_name);
    }
    
    protected function snakeToPascal($string) {
        // Разбиваем по _
        $words = explode('_', $string);
        // Делаем первую букву каждого слова заглавной
        $words_uc = array_map('ucfirst', $words);
        // Склеиваем обратно
        return implode('', $words_uc);
    }
    
}
