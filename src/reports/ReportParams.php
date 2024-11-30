<?php

namespace losthost\BlagoBot\reports;

use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param_value;
use losthost\telle\Bot;
use losthost\BlagoBot\data\x_omsu;
use losthost\BlagoBot\data\x_category;
use Exception;

class ReportParams {
    
    protected $params;

    public function __construct(?array $params=null) {

        $this->params = [];
        $session_params = is_null($params) ? Bot::$session->get('data') : $params;
        $report = new report(['id' => Bot::$session->get('command')]);
        
        foreach ($report->paramsArray() as $param) {
            
            $this->params[$param->getName()] = [
                'name' => $param->getName(), 
                'title' => $param->getTitle(), 
                'values' => []
            ];
            
            if (!isset($session_params[$param->getName()])) {
                continue;
            }
            
            foreach ($session_params[$param->getName()] as $value_id) {
                if ($param->getValueSet() == 'omsu') {
                    $value = new x_omsu(['id' => $value_id]);
                    $this->params[$param->getName()]['values'][] = [
                        'id' => $value->id, 
                        'title' => $value->name, 
                        'value' => $value->name
                    ];
                } elseif ($param->getValueSet() == 'activity') {
                    $value = new x_category(['id' => $value_id]);
                    $this->params[$param->getName()]['values'][] = [
                        'id' => $value->id, 
                        'title' => $value->name, 
                        'value' => $value->name
                    ];
                } elseif (is_a($param->getValueSet(), \losthost\DB\DBObject::class, true)) {
                    $class = $param->value_set;
                    $value = new $class(['id' => $value_id]);
                    $this->params[$param->getName()]['values'][] = [
                        'id' => $value->id, 
                        'title' => $value->getTitle(), 
                        'value' => $value->getValue()
                    ];
                } elseif (is_array($param->getValueSet())) {
                    $value = $param->valueByValue($value_id);
                    $this->params[$param->getName()]['values'][] = [
                        'id' => $value->getId(),
                        'title' => $value->getTitle(),
                        'value' => $value->getValue()
                    ];
                } else {
                    $value = new report_param_value(['id' => $value_id]);
                    $this->params[$param->getName()]['values'][] = [
                        'id' => $value->id, 
                        'title' => $value->title, 
                        'value' => $value->value
                    ];
                }
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
