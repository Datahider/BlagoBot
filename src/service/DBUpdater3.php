<?php

namespace losthost\BlagoBot\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use losthost\BlagoBot\data\x_object;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DBUpdater3 extends DBUpdater2 {

    const  WORKSHEET_NAME = 'Отчет1';
    
    protected function loadDB(Worksheet &$sheet) {
        
        $row_iterator = $sheet->getRowIterator(5);
        $row_num = 4;
        
        foreach ($row_iterator as $row) {
            $row_num++;
            $cell_iterator = $row->getCellIterator();
            $cells = [];
            foreach ($cell_iterator as $cell) {
                $cells[] = $cell->getValue();
            }
            
            $m = [];
            if (!preg_match("/^\'?(\d+\.\d+)$/", $cells[3], $m)) {
                error_log("Incorrect uin: $cells[3]");
                continue;
            }
            
            try {
                $object = new x_object(['uin' => $m[1]]);
                $object->ready_percent = $cells[8];

                $object->open_date_planned = $cells[6] ? Date::excelToDateTimeObject($cells[6]) : null;
                $object->open_date_fact = $cells[7] ? Date::excelToDateTimeObject($cells[7]) : null;
                $object->write();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num");
            } catch (\TypeError $e) {
                throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num");
            }
        }
        
    }
    
}
