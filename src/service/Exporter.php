<?php

namespace losthost\BlagoBot\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use losthost\BlagoBot\service\xls\CellFormat;
use losthost\telle\Bot;

class Exporter {
    
    const SECRET = 'psadQUj6v2LNxCbfLwrm8mThCXpf';
    
    protected $result;
    protected $sheet;
    protected $current_row;
    protected $data_group_column_index;
    protected $last_data_group_value;
    
    protected $gp_date;
    protected $db_date;
    protected $status_date;
    
    public function __construct() {
        $this->last_data_group_value = self::SECRET;
        $this->gp_date = Bot::param('gp_date', 'ГП от НЕОПРЕДЕЛЕНО');
        $this->db_date = Bot::param('db_date', 'НЕОПРЕДЕЛЕНО');
        $this->status_date = Bot::param('status_date', 'НЕОПРЕДЕЛЕНО');
    }

    public function export($result) {
    
        $spreadsheet = new Spreadsheet();
        $this->sheet = $spreadsheet->getActiveSheet();
        $this->result = $result;
        
        $this->exportHeader();
        $this->exportTable();

        return $spreadsheet;
    }

    protected function exportHeader() {
        
        $last_column = count($this->result->columns);
        
        $this->current_row = 1;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue('Дата и время выгрузки '. $this->result->summary->getDateGenerated()->format('d.m.Y H:i'));
        $cell->getStyle()->applyFromArray(CellFormat::ReportDate);
        
        $this->current_row++;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue('Денежные величины заполнены в тыс. руб.');

        $this->current_row++;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue('Количественные величины заполнены в шт.');

        $this->current_row += 2;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue($this->gp_date);
        $cell->getStyle()->applyFromArray(CellFormat::ReportDBInfo);

        $this->current_row++;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue('База от '. $this->db_date);
        $cell->getStyle()->applyFromArray(CellFormat::ReportDBInfo);

        $this->current_row++;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue('Статус от '. $this->status_date);
        $cell->getStyle()->applyFromArray(CellFormat::ReportDBInfo);

        $this->current_row++;
        foreach ($this->result->summary->getParams() as $param) {
            $this->current_row++;
            $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
            $cell = $this->sheet->getCell([1,$this->current_row]);
            $cell->setValue("{$param['title']}: {$param['value']}");
            $cell->getStyle()->applyFromArray(CellFormat::ReportParams);
        }

        $note = $this->result->summary->getNote();
        if ($note) {
            $this->current_row += 2;
            $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
            $cell = $this->sheet->getCell([1,$this->current_row]);
            $cell->setValue($note);
            $cell->getStyle()->applyFromArray(CellFormat::ReportNote);
        }
        
        $this->current_row +=2;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue($this->result->summary->getTitle());
        $cell->getStyle()->applyFromArray(CellFormat::ReportHeader);
    }
    
    protected function exportTable() {

        $this->current_row += 2; // пропускаем строку перед таблицей
        foreach ($this->result->columns as $cc => $column) {
            $cell = $this->sheet->getCell([$cc+1, $this->current_row]);
            $cell->setValue($column->getTitle());
            $cell->getStyle()->applyFromArray($column->getHeaderFormat());
            
            $width = $column->getWidth();
            if ($width != 0) {
                $this->sheet->getColumnDimension($cell->getColumn())->setWidth($width);
            }
            $column->resetTotals([0,1]);
            if ($column->getDataGroupIndex()) {
                $this->data_group_column_index = $cc;
            }
        }

        $dark = false;
        $subtotals = false;
        $totals = false;
        
        foreach ($this->result->data as $index => $row_data) {
            if ($this->last_data_group_value == self::SECRET) {
                $this->last_data_group_value = $row_data[$this->data_group_column_index];
            }
            $subtotals = $this->printTotals($row_data) || $subtotals;
            $this->current_row++;
            foreach ($row_data as $cc => $value) {
                if (isset($this->result->columns[$cc])) {
                    $column = $this->result->columns[$cc];
                    $cell = $this->sheet->getCell([$cc+1, $this->current_row]);
                    if ($value != 0) {
                        $cell->setValue($value);
                    }
                    $totals = $column->add2Totals($value) || $totals;
                    $format = $dark ? $this->makeDarker($column->getDataFormat()) : $column->getDataFormat();
                    $cell->getStyle()->applyFromArray($this->nfTrick($format, $value));
                }
            }
            $dark = !$dark;
        }
        
        if ($subtotals) {
            $fake_row[$this->data_group_column_index] = self::SECRET;
            $this->printTotals($fake_row);
        }
        
        if ($totals) {
            $this->printTotals();
        }
    }
    
    protected function nfTrick(array $format, mixed $sum) {
        if (is_numeric($sum) 
                &&isset($format['numberFormat']['formatCode']) 
                && $format['numberFormat']['formatCode'] == '# ##0') {
            if ($sum >= 1000000000) {
                $format['numberFormat']['formatCode'] = '# ### ### ##0';
            } elseif ($sum >= 1000000) {
                $format['numberFormat']['formatCode'] = '# ### ##0';
            } elseif ($sum < 1000) {
                $format['numberFormat']['formatCode'] = '#';
            }
        }
        return $format;
    }

    protected function printTotals(?array $row_data=null) {
        
        if (is_null($row_data)) {
            $index = 0;
        } elseif ($row_data[$this->data_group_column_index] != $this->last_data_group_value) {
            // Переназначим последнее значение позже, после вывода его в таблицу
            $index = 1;
        } else {
            return false;
        }
        
        $this->current_row++;
        foreach ($this->result->columns as $cc => $column) {
            $cell = $this->sheet->getCell([$cc+1, $this->current_row]);
            if ($cc == $this->data_group_column_index && $index == 0) {
                $cell->setValue('Всего');
            } elseif ($cc == $this->data_group_column_index && $index == 1) {
                $cell->setValue('Итого '. $this->last_data_group_value);
                $this->last_data_group_value = $row_data[$this->data_group_column_index];
            } else {
                $cell->setValue($column->getTotals($index, true));
            }
            $cell->getStyle()->applyFromArray($this->nfTrick($column->getTotalFormat($index), $cell->getValue()));
        }
        
        return true;
    }
    
    protected function makeDarker($format) {
        if (!isset($format['fill']['color']['argb'])) {
            return;
        }
        
        $initial = $format['fill']['color']['argb'];
        $dec = hexdec($initial);
        $dec -= 2105376;
        $format['fill']['color']['argb'] = dechex($dec);
        return $format;
    }
}
