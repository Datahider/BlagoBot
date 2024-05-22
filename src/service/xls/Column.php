<?php

namespace losthost\BlagoBot\service\xls;

use losthost\BlagoBot\service\AbstractTotalsCollector;

class Column {
    
    protected string $title;                         
    protected float $width;
    protected array $header_format;
    protected array $data_format;
    protected array $totals_formats;
    protected bool|AbstractTotalsCollector $collect_totals;
    protected ?ColumnGroup $column_group;
    protected ?int $data_group_index;
    protected array $totals;
    
    public function __construct(
            string $title,
            float $width,
            array $header_format, 
            array $data_format, 
            ?array $totals_formats = null,
            bool|AbstractTotalsCollector $collect_totals = false,
            ?ColumnGroup $column_group = null,
            ?int $data_group_index = null,
            ) {
        
        $this->title = $title;
        $this->width = $width;
        $this->header_format = $header_format;
        $this->data_format = $data_format;
        $this->totals_formats = is_null($totals_formats) ? [] : $totals_formats;
        $this->collect_totals = $collect_totals;
        $this->column_group = $column_group;
        $this->data_group_index = $data_group_index;
        $this->totals = [];
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getWidth() {
        return $this->width;
    }
    
    public function getHeaderFormat() {
        return $this->header_format;
    }
    
    public function getDataFormat() {
        return $this->data_format;
    }
    
    public function getTotalFormat(int $index) {
        return isset($this->totals_formats[$index]) ? $this->totals_formats[$index] : [];
    }
    
    public function resetTotals(array $indexes) {
        foreach ($indexes as $index) {
            $this->totals[$index] = 0;
        }
    }
    
    public function add2Totals($value) {
        if (is_a($this->collect_totals, AbstractTotalsCollector::class)) {
            foreach (array_keys($this->totals) as $key) {
                $this->totals[$key] = $this->collect_totals->calculate($key, $value);
            }
            return true;
        } elseif ($this->collect_totals) {
            foreach (array_keys($this->totals) as $key) {
                $this->totals[$key] += $value;
            }
            return true;
        }
        return false;
    }
    
    public function getTotals($index, $with_reset = false) {
        if (!$this->collect_totals) {
            return null;
        }
        
        $result = $this->totals[$index];
        if ($with_reset) {
            $this->totals[$index] = 0;
        }
        return $result;
    }
    
    public function getDataGroupIndex() {
        return $this->data_group_index;
    }
}
