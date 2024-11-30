<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\service\DBUpdater;
use losthost\BlagoBot\data\x_object;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use losthost\BlagoBot\data\x_responsible;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DBUpdater2 extends DBUpdater {
    
    const  WORKSHEET_NAME = 'База';

    public function update(Spreadsheet &$spreadsheet) {
    
        $sheet = $spreadsheet->getSheetByName(static::WORKSHEET_NAME);
        if (!$sheet) {
            throw new \Exception(static::NOT_MY_FILE_EXCEPTION);
        }
        $this->updateDB($sheet);

    }
    
    protected function loadDB(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet &$sheet) {

        $row_iterator = $sheet->getRowIterator(4);
        $row_num = 3;
        
        foreach ($row_iterator as $row) {
            $row_num++;
            $cell_iterator = $row->getCellIterator();
            $cells = [];
            foreach ($cell_iterator as $cell) {
                $cells[] = $cell->getValue();
            }
            
            if (!preg_match("/^\d+\.\d+$/", $cells[1])) {
                error_log("Incorrect uin: $cells[1]");
                continue;
            }
            
            try {
                $object = new x_object(['uin' => $cells[1]]);
//                $object->x_responsible_id = x_responsible::getByFio($cells[6])->id;
//                $object->open_date_fact = $cells[9] ? Date::excelToDateTimeObject($cells[9]) : null;
                $object->moge_in_plan = $cells[18] ? Date::excelToDateTimeObject($cells[18]) : null;
                $object->moge_in_fact = $cells[20] ? Date::excelToDateTimeObject($cells[20]) : null;
                $object->moge_out_plan = $cells[21] ? Date::excelToDateTimeObject($cells[21]) : null;
                $object->moge_out_fact = $cells[23] ? Date::excelToDateTimeObject($cells[23]) : null;
                $object->rgmin_in_plan = $cells[28] ? Date::excelToDateTimeObject($cells[28]) : null;
                $object->rgmin_in_fact = $cells[30] ? Date::excelToDateTimeObject($cells[30]) : null;
                $object->psmr_plan = $cells[32] ? Date::excelToDateTimeObject($cells[32]) : null;
                $object->psmr_fact = $cells[34] ? Date::excelToDateTimeObject($cells[34]) : null;
                $object->ksmr_plan = $cells[35] ? Date::excelToDateTimeObject($cells[35]) : null;
                $object->ksmr_fact = $cells[37] ? Date::excelToDateTimeObject($cells[37]) : null;
                $object->purchase_level = $cells[27];
                $object->write();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num");
            } catch (\TypeError $e) {
                throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num");
            }
        }
    }
}
