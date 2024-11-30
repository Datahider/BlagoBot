<?php

namespace losthost\BlagoBot\reports;

use losthost\telle\Bot;
use losthost\BlagoBot\service\ReportSummary;
use Exception;
use stdClass;

abstract class AbstractReport {
    
    const RESULT_TYPE_XLSX = 0;
    const RESULT_TYPE_SHOW = 1;
    const RESULT_TYPE_CUSTOM = 2;

    protected ?array $params;
    
    abstract protected function reportColumns() : array;
    abstract protected function reportData($params) : array;
    abstract protected function reportSummary($params) : ReportSummary;

    abstract protected function resultType() : int|string;
    abstract protected function initParams();

    abstract protected function checkParamErrors($params) : false|array;

    public function __construct() {
        $this->initParams();
    }
    
    public function getParams() : ?array {
        return $this->params;
    }
    
    public function areMandatoryOk() {
        $params = $this->getParams();
        if ($params === null) {
            return true;
        }
        $param_data = Bot::$session->get('data');
        foreach ($params as $param) {
            if ($param->isMandatory() && empty($param_data[$param->getName()])) {
                return false;
            }
        }
        return true;
    }
    
    public function getParamIndexByClass($class_or_object) {
        if (!is_string($class_or_object)) {
            $class_or_object = get_class($class_or_object);
        }
        
        $index = 0;
        foreach ($this->getParams() as $param) {
            if (get_class($param) == $class_or_object) {
                return $index;
            }
            $index++;
        }
        return null;
    }

    public function setDefaultParamValues() {
        $data = Bot::$session->get('data');
        foreach ($this->getParams() as $param) {
            if (!isset($data[$param->getName()])) {
                $data[$param->getName()] = $param->getDefaultValues();
            }
        }
        
        Bot::$session->set('data', $data);
    }
    
    public function build() : stdClass {
        
        $report_params = Bot::$session->get('data');
        $errors = $this->checkParamErrors($report_params);
        if ($errors !== false) {
            return (object)[
                'params' => $report_params,
                'ok' => false,
                'errors' => $errors
            ];
        }
        
        try {
            return (object)[
                'params' => $report_params,
                'ok' => true,
                'columns' => $this->reportColumns(),
                'data' => $this->reportData($report_params),
                'result_type' => $this->resultType(),
                'summary' => $this->reportSummary($report_params)
                    
            ];
        } catch (Exception $ex) {
            return (object)[
                'params' => $report_params,
                'ok' => false,
                'errors' => [$ex->getMessage()]
            ];
        }
    }
   
}
