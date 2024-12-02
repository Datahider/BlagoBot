<?php

namespace losthost\BlagoBot\service;

use PhpOffice\PhpSpreadsheet\Style\Color;

class ExporterReady extends Exporter {
    
    protected function exportHeader() {
        parent::exportHeader();
        $this->sheet->removeRow(2);
        $this->current_row--;
    }
    
    protected function exportTable() {
        $starting_row = $this->current_row+3;
        parent::exportTable();
        $ending_row = $this->current_row;
        
        $this->colorize($starting_row, $ending_row);
    }
    
    protected function colorize($start, $end) {
        
        for($row_index = $start; $row_index<=$end; $row_index++) {
            $cell = $this->sheet->getCell([1, $row_index]);

            if (!is_numeric($cell->getValue())) {
                continue;
            }
            
            $row_data = $this->result->data[$cell->getValue()-1];
            $open = $row_data[7];
            $fact = $row_data[6];
            $percent = $row_data[8];
            
            if (!$fact && date_create($open)->getTimestamp() < date_create()->getTimestamp()) {
                // All red
                $this->sheet->getCell([1, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([2, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([3, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([4, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([5, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([6, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([7, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([8, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
                $this->sheet->getCell([9, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
            } elseif ($fact && $percent < 1) {
                // Percent red
                $this->sheet->getCell([9, $row_index])->getStyle()->applyFromArray(['font' => ['color' => ['argb' => Color::COLOR_RED]]]);
            }
        }
    }
}
