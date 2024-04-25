<?php

namespace losthost\BlagoBot\service\xls;

class Column {
    
    protected string $title;                         
    protected array $header_format;
    protected array $data_format;
    protected array $totals_formats;
    protected bool $collect_totals;
    protected ?ColumnGroup $column_group;
    protected ?int $data_group_index;
    protected array $totals;
    
    public function __construct(
            string $title,
            array $header_format, 
            array $data_format, 
            ?array $totals_formats = null,
            bool $collect_totals = false,
            ?ColumnGroup $column_group = null,
            ?int $data_group_index = null,
            ) {
        
        $this->title = $title;
        $this->$header_format = $header_format;
        $this->$data_format = $data_format;
        $this->totals_formats = is_null($totals_formats) ? [] : $totals_formats;
        $this->collect_totals = $collect_totals;
        $this->column_group = $column_group;
        $this->data_group_index = $data_group_index;
        $this->totals = [];
    }
    
    public function resetTotals(array $indexes) {
        foreach ($indexes as $index) {
            $this->totals[$index] = 0;
        }
    }
    
    public function add2Totals($value) {
        foreach (array_keys($this->totals) as $key) {
            $this->totals[$key] += $value;
        }
    }
    
    public function getTotals($index, $with_reset = false) {
        $result = $this->totals[$index];
        if ($with_reset) {
            $this->totals[$index] = 0;
        }
        return $result;
    }
}
