<?php

namespace losthost\BlagoBot\reports;

use losthost\telle\Bot;
use Exception;
use stdClass;

abstract class AbstractReport {
    
    abstract protected function reportColumns() : array;
    abstract protected function reportData($params) : array;
    abstract protected function checkParamErrors($params) : false|array;

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
                'data' => $this->reportData($report_params)
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
