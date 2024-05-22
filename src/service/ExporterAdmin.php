<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\service\xls\CellFormat;

class ExporterAdmin extends Exporter {
    
    protected function exportHeader() {
        $last_column = count($this->result->columns);
        
        $this->current_row = 1;
        $this->sheet->mergeCells([1, $this->current_row, $last_column, $this->current_row]);
        $cell = $this->sheet->getCell([1,$this->current_row]);
        $cell->setValue('Дата и время выгрузки '. $this->result->summary->getDateGenerated()->format('d.m.Y H:i'));
        $cell->getStyle()->applyFromArray(CellFormat::ReportDate);

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
}
