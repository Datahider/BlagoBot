<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param_value;
use losthost\telle\Bot;
use Exception;

class ReportParams {
    
    protected $params;

    public function __construct(?array $params=null) {

        $this->params = [];
        $session_params = is_null($params) ? Bot::$session->get('data') : $params;
        $report = new report(['id' => Bot::$session->get('command')]);
        
        foreach ($report->paramsArray() as $param) {
            
            $this->params[$param->name] = [
                'name' => $param->name, 
                'title' => $param->title, 
                'values' => []
            ];
            
            if (!isset($session_params[$param->name])) {
                continue;
            }
            
            foreach ($session_params[$param->name] as $value_id) {
                $value = new report_param_value(['id' => $value_id]);
                $this->params[$param->name]['values'][] = [
                    'id' => $value->id, 
                    'title' => $value->title, 
                    'value' => $value->value
                ];
            }
        }
    }
    
    public function paramTitlesAsString(string $name, string $delimiter='; ') : string {
        
        $result = $this->paramTitlesAsArray($name);
        if (count($result) == 0) {
            return "-";
        }
        return implode($delimiter, $result);
    }
    
    public function paramTitlesAsArray(string $name) : array {
        $result = [];
        foreach ($this->params[$name]['values'] as $value) {
            $result[] = $value['title'];
        }
        return $result;
    }
    
    public function paramValuesAsArray(string $name) : array {
        $result = [];
        foreach ($this->params[$name]['values'] as $value) {
            $result[] = $value['value'];
        }
        return $result;
    }
    
    public function paramTitle(string $name) : string {
        return $this->params[$name]['title'];
    }
}
