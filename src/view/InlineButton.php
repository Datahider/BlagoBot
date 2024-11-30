<?php

namespace losthost\BlagoBot\view;

use losthost\BlagoBot\data\menu;
use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param;
use losthost\BlagoBot\data\report_param_value;
use losthost\telle\Bot;
use losthost\BlagoBot\data\x_omsu;
use losthost\BlagoBot\data\x_category;
use losthost\BlagoBot\data\x_responsible;
use losthost\BlagoBot\params\AbstractParamDescription;
use losthost\BlagoBot\params\ParamValue;
use Exception;

use function \losthost\BlagoBot\getClassIndex;

class InlineButton {
    
    const MB_SUBMENU = 0;
    const MB_REPORT = 1;
    const MB_PARAM = 2;
    const MB_VALUE = 3;
    const MB_NEW_PARAM = 4;
    const MB_NEW_VALUE = 5;
    
    protected $type;
    protected $object;
    protected $param;

    protected $icon_delimiter = ' ';
    protected $icon_selected = '✅';
    protected $icon_unselected = '🔲';
    protected $icon_warning = '⚠️';


    // Interface
public function __construct(ParamValue|AbstractParamDescription|menu|report|report_param|report_param_value|x_omsu|x_category|x_responsible|string $object, null|report_param|AbstractParamDescription $param=null) {
        if (is_a($object, menu::class)) {
            $this->type = self::MB_SUBMENU;
        } elseif (is_a($object, report::class)) {
            $this->type = self::MB_REPORT;
        } elseif (is_a($object, report_param::class)) {
            $this->type = self::MB_PARAM;
        } elseif (is_a($object, report_param_value::class)) {
            $this->type = self::MB_VALUE;
        } elseif (is_a($object, x_omsu::class)) {
            $this->type = self::MB_VALUE;
        } elseif (is_a($object, x_category::class)) {
            $this->type = self::MB_VALUE;
        } elseif (is_a($object, x_responsible::class)) {
            $this->type = self::MB_VALUE;
        } elseif (is_a($object, AbstractParamDescription::class)) {
            $this->type = self::MB_NEW_PARAM;
        } elseif (is_a($object, ParamValue::class)) {
            $this->type = self::MB_NEW_VALUE;
        } elseif (is_string($object)) {
            $this->setupByString($object);
            return;
        }
        $this->object = $object;
        $this->param = $param;
    }
    public function buttonData($text=null) {
        if (!$text) {
            $text = $this->getButtonText();
        }
        
        switch ($this->type) {
            case self::MB_SUBMENU:
                $data = "submenu_{$this->object->id}";
                break;
            case self::MB_REPORT:
                $data = "report_{$this->object->id}";
                break;
            case self::MB_PARAM:
                $data = "param_{$this->object->id}";
                break;
            case self::MB_NEW_PARAM:
                $report_class = $this->object->getReportClass();
                $report_index = getClassIndex($report_class);
                $report = new $report_class();
                $param_index = $report->getParamIndexByClass($this->object);
                $data = "newparam_{$report_index}_$param_index";
                break;
            case self::MB_NEW_VALUE:
                $report_class = $this->param->getReportClass();
                $report_index = getClassIndex($report_class);
                $report = new $report_class();
                $param_index = $report->getParamIndexByClass($this->param);
                $data = "newvalue_{$report_index}_{$param_index}_{$this->object->getValue()}";
                break;
            case self::MB_VALUE:
                $data = "value_{$this->object->id}_{$this->param->id}";
                break;
            default:
                throw new Exception('Unknown button type.');
        }
        
        return [ 'text' => $text, 'callback_data' => $data ];
    }
    
    // Setters
    public function setIconDelimiter($string) {
        $this->icon_delimiter = $string;
    }
    public function setIconSelected($string) {
        $this->icon_selected = $string;
    }
    public function setIconUnselected($string) {
        $this->icon_unselected = $string;
    }
    // Getters
    public function getType() {
        return $this->type;
    }
    public function getObject() {
        return $this->object;
    }
    public function getParam() {
        return $this->param;
    }
    
    // Protected
    protected function setupByString(string $string) {
        
        $m = [];
        
        if (preg_match("/^(submenu_|report_|param_)(\S+)$/", $string, $m)) {
            $this->param = null;
            switch ($m[1]) {
                case 'submenu_':
                    $this->object = new menu(['id' => $m[2]]);
                    $this->type = self::MB_SUBMENU;
                    break;
                case 'report_':
                    $this->object = new report(['id' => $m[2]]);
                    $this->type = self::MB_REPORT;
                    break;
                case 'param_':
                    $this->object = new report_param(['id' => $m[2]]);
                    $this->type = self::MB_PARAM;
                    break;
            }
        } elseif (preg_match("/^value_(\d+)_(\d+)$/", $string, $m)) {
            $this->object = new report_param_value(['id' => $m[1]]);
            $this->param = new report_param(['id' => $m[2]]);
            $this->type = self::MB_VALUE;
        } elseif (preg_match("/^newparam_(\d+)_(\d+)$/", $string, $m)) {
            $report_class = getClassIndex($m[1]);
            $report = new $report_class();
            $this->object = $report->getParams()[$m[2]];
            $this->type = self::MB_NEW_PARAM;
        } elseif (preg_match("/^newvalue_(\d+)_(\d+)_(.+)$/", $string, $m)) {
            $report_class = getClassIndex($m[1]);
            $report = new $report_class();
            $this->param = $report->getParams()[$m[2]];
            $this->object = $this->param->valueByValue($m[3]);
            $this->type = self::MB_NEW_VALUE;
        } else {
            throw new Exception('Invalid button data.');
        }
    }
    protected function getButtonText() {
        
        switch ($this->type) {
            case self::MB_VALUE:
            case self::MB_NEW_VALUE:
                return $this->getValueIcon(). $this->icon_delimiter. $this->object->getTitle();
            case self::MB_PARAM:
            case self::MB_NEW_PARAM:
                $text = $this->getParamIcon();
                return $text ? $text. $this->icon_delimiter. $this->object->getTitle() : $this->object->getTitle();
            default:
                return $this->object->getTitle();
        }
    }
    protected function getValueIcon() {
        $param_values = Bot::$session->get('data');
        
        if (empty($param_values[$this->param->getName()])) {
            return $this->icon_unselected;
        } elseif (array_search($this->object->getId(), $param_values[$this->param->getName()]) === false) {
            return $this->icon_unselected;
        }
        
        return $this->icon_selected;
    }
    
    protected function getParamIcon() {
        $param_values = Bot::$session->get('data');
        
        if (empty($param_values[$this->object->getName()]) && $this->object->isMandatory()) {
            return $this->icon_warning;
        }
        
        return '';
    }
    
}
